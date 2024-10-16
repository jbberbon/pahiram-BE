<?php

namespace App\Services\BorrowRequestService;
use App\Models\BorrowedItem;
use App\Models\BorrowPurpose;
use App\Models\BorrowTransaction;
use App\Models\Department;
use App\Models\User;
use App\Services\RetrieveStatusService\BorrowedItemStatusService;
use App\Services\RetrieveStatusService\BorrowTransactionStatusService;
use Illuminate\Support\Facades\Auth;

class BorrowRequestFinalizationService
{
    private $borrowRequestService;
    // Transac Statuses
    private $pendingEndorserApprovalTransactionId;
    private $pendingBorrowingApprovalTransactionId;
    private $approvedTransactionId;
    private $onGoingTransactionId;
    public function __construct(
        BorrowRequestHelperService $borrowRequestService,
    ) {
        $this->borrowRequestService = $borrowRequestService;

        // Transac Statuses
        $this->pendingEndorserApprovalTransactionId = BorrowTransactionStatusService::getPendingEndorserApprovalTransactionId();
        $this->pendingBorrowingApprovalTransactionId = BorrowTransactionStatusService::getPendingBorrowingApprovalTransactionId();

        $this->approvedTransactionId = BorrowTransactionStatusService::getApprovedTransactionId();
        $this->onGoingTransactionId = BorrowTransactionStatusService::getOnGoingTransactionId();
    }

    /**
     *  06. Insert new borrowing transaction
     */
    public function insertBorrowingTransactionForSingleOffice(array $validatedData): array|string
    {
        try {
            unset($validatedData['items']);
            $purposeId = $validatedData['purpose_id'];
            $userDefinedPurpose = $validatedData['user_defined_purpose'];

            // QUERY the purpose and department IDS
            $departmentId = Department::getIdBasedOnAcronym($validatedData['department']);

            $user = Auth::user();
            $employeeEmail = "@apc.edu.ph";

            $newBorrowRequestArgs = null;
            // Convert APC_ID to Pahiram ID
            // Endorser is Indicated in the request
            if (isset($validatedData['endorsed_by'])) {
                $newBorrowRequestArgs = [
                    'endorsed_by' => $validatedData['endorsed_by'],
                    'borrower_id' => auth()->id(),
                    'transac_status_id' =>
                        strpos($user->email, $employeeEmail) ?
                        $this->approvedTransactionId :
                        $this->pendingEndorserApprovalTransactionId,
                    'purpose_id' => $purposeId,
                    'department_id' => $departmentId,
                    'user_defined_purpose' => $userDefinedPurpose
                ];
            } else {
                $newBorrowRequestArgs = [
                    'borrower_id' => auth()->id(),
                    'transac_status_id' =>
                        strpos($user->email, $employeeEmail) ?
                        $this->approvedTransactionId :
                        $this->pendingBorrowingApprovalTransactionId,
                    'purpose_id' => $purposeId,
                    'department_id' => $departmentId,
                    'user_defined_purpose' => $userDefinedPurpose
                ];
            }

            return BorrowTransaction::create($newBorrowRequestArgs)->toArray();
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Something went wrong while adding transaction',
                'error' => $e->getMessage(),
                'method' => "POST"
            ];
        }
    }
    public function processRequestedItems(array $requestedItems)
    {
        // 01. Get all items with "active" status in items table 
        $activeItems = $this
            ->borrowRequestService
            ->getActiveItems($requestedItems);

        // 02. EDGE CASE: Check for empty active item_id array in activeItems variable
        $emptyActiveItemsIdArray = $this
            ->borrowRequestService
            ->checkActiveItemsForEmptyItemIdField($activeItems);

        if (is_array($emptyActiveItemsIdArray)) {
            return $emptyActiveItemsIdArray;
        }

        // 03. Check borrowed_items if which ones are available on that date
        $availableItems = $this
            ->borrowRequestService
            ->getAvailableItems($activeItems);

        // 04. Requested qty > available items on schedule (Fail)
        $isRequestQtyMoreThanAvailableQty = $this
            ->borrowRequestService
            ->checkRequestQtyAndAvailableQty($availableItems);

        if (is_array($isRequestQtyMoreThanAvailableQty)) {
            return $isRequestQtyMoreThanAvailableQty;
        }

        // 05. Requested qty < available items on schedule (SHUFFLE then Choose)
        $chosenItems = $this
            ->borrowRequestService
            ->shuffleAvailableItems($availableItems);


        // 06. Just return chosen items
        return $chosenItems;
    }

    /**
     * Insert new borrowed items
     */
    public function insertNewBorrowedItems(array $chosenItems, string $borrowRequestId): array
    {
        try {
            $newBorrowedItems = [];
            $pendingStatusId = BorrowedItemStatusService::getPendingStatusId();
            $approvedStatusId = BorrowedItemStatusService::getApprovedStatusId();

            $user = Auth::user();
            $employeeEmail = "@apc.edu.ph";
            foreach ($chosenItems as $borrowedItem) {
                // Remove qty field
                unset($borrowedItem['quantity']);

                foreach ($borrowedItem['item_id'] as $itemId) {
                    $newBorrowedItemsArgs = [
                        'borrowing_transac_id' => $borrowRequestId,
                        'item_id' => $itemId,
                        'start_date' => $borrowedItem['start_date'],
                        'due_date' => $borrowedItem['return_date'],
                        'borrowed_item_status_id' =>
                            strpos($user->email, $employeeEmail) ?
                            $approvedStatusId :
                            $pendingStatusId
                    ];
                    $newBorrowedItems[$itemId] = BorrowedItem::create($newBorrowedItemsArgs);
                }
            }
            return $newBorrowedItems;
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Something went wrong while adding items',
                'error' => $e->getMessage(),
                'method' => "POST"
            ];
        }
    }

    // Insert multiple transactions and items
    public function insertTransactionAndBorrowedItemsForMultipleOffices(
        array $validatedDataWithoutOffice,
        array $groupedFinalItemList
    ): int|array|string {
        try {
            foreach ($groupedFinalItemList as $officeAcronym => $chosenItem) {
                $newBorrowRequest = self::insertBorrowingTransactionForSingleOffice(
                    validatedData:
                    [
                        ...$validatedDataWithoutOffice,
                        'department' => $officeAcronym
                    ]
                );

                $newBorrowedItems = self::insertNewBorrowedItems(
                    chosenItems: $chosenItem,
                    // borrowRequestId: $newBorrowRequest->id
                    borrowRequestId: $newBorrowRequest['id']
                );

                if (isset($newBorrowRequest['status'])) {
                    return $newBorrowRequest;
                }

                if (isset($newBorrowedItems['status'])) {
                    return $newBorrowedItems;
                }

                if (
                    isset($newBorrowedItems['status']) &&
                    isset($newBorrowRequest['status'])
                ) {
                    return [
                        'status' => false,
                        'message' => 'Something went wrong while adding your request',
                        'method' => 'POST',
                    ];
                }
            }
            return (int) count($groupedFinalItemList);

        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Something went wrong while adding your request',
                'error' => $e->getMessage(),
                'method' => 'POST',
            ];
        }
    }
}