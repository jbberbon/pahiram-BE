<?php

namespace App\Services\BorrowRequestService;

use App\Models\BorrowedItem;
use App\Models\BorrowPurpose;
use App\Models\BorrowTransaction;
use App\Models\Department;
use App\Models\Item;
use App\Models\ItemGroup;
use App\Models\User;
use App\Services\RetrieveStatusService\BorrowedItemStatusService;
use App\Services\RetrieveStatusService\ItemStatusService;
use App\Services\ItemAvailability;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use App\Services\RetrieveStatusService\BorrowTransactionStatusService;
use Illuminate\Support\Facades\Auth;

class SubmitBorrowRequestService
{
    private $itemAvailability;
    const MAX_ACTIVE_TRANSACTIONS = 4;

    // Item Statuses
    private $activeItemStatusId;

    // Transac Statuses
    private $pendingEndorserApprovalTransactionId;
    private $pendingBorrowingApprovalTransactionId;
    private $approvedTransactionId;
    private $onGoingTransactionId;

    public function __construct(ItemAvailability $itemAvailability)
    {
        $this->itemAvailability = $itemAvailability;

        // Transac Statuses
        $this->pendingEndorserApprovalTransactionId = BorrowTransactionStatusService::getPendingEndorserApprovalTransactionId();
        $this->pendingBorrowingApprovalTransactionId = BorrowTransactionStatusService::getPendingBorrowingApprovalTransactionId();

        $this->approvedTransactionId = BorrowTransactionStatusService::getApprovedTransactionId();
        $this->onGoingTransactionId = BorrowTransactionStatusService::geOnGoingTransactionId();

        // Item Statuses
        $this->activeItemStatusId = ItemStatusService::getActiveStatusId();
    }
    /**
     *  01. Check if user has > 3 active transactions
     */
    public function checkMaxTransactions($userId): null|JsonResponse
    {
        $transactionStatusIds = [
            $this->approvedTransactionId,
            $this->pendingEndorserApprovalTransactionId,
            $this->pendingBorrowingApprovalTransactionId,
            $this->onGoingTransactionId
        ];
        $activeTransactions = BorrowTransaction::where('borrower_id', $userId)
            ->whereIn('transac_status_id', $transactionStatusIds)
            ->count();

        if ($activeTransactions >= self::MAX_ACTIVE_TRANSACTIONS) {
            return response()->json([
                'status' => false,
                'message' => 'Complete your other 3 or more transactions first.',
                'method' => 'GET'
            ], Response::HTTP_UNAUTHORIZED);
        }
        // No issue
        return null;
    }

    /**
     *  02. Get all items with active status in items tb
     *  Note: Checking if Requested Qty > Active Status Items is in Validation Requests
     */
    public function getActiveItems(array $requestedItems): array
    {
        $activeItems = [];

        foreach ($requestedItems as $item) {
            $itemGroupId = $item['item_group_id'];
            $activeItems[$itemGroupId] = [
                'item_id' => Item::where('item_group_id', $itemGroupId)
                    ->where('item_status_id', $this->activeItemStatusId)
                    ->pluck('id'),
                'start_date' => $item['start_date'],
                'return_date' => $item['return_date'],
                'quantity' => $item['quantity']
            ];
        }
        return $activeItems;
    }

    /**
     * 02.1. EDGE CASE: Check if getActive items returned an array with an empty item_id array value
     */
    public function checkActiveItemsForEmptyItemIdField(array $activeItems): ?JsonResponse
    {
        $hasEmptyItemId = false;

        foreach ($activeItems as $itemGroupId => $items) {
            // Check if the item_id field is empty
            if (isset($items['item_id']) && empty($items['item_id'])) {
                // Set the flag to true as we found at least one empty item_id array
                $hasEmptyItemId = true;
            }
        }

        // If at least one empty item_id array was found, return a JSON response
        if ($hasEmptyItemId) {
            return response()->json([
                'status' => false,
                'message' => 'Some item groups have an empty item_id array.',
                'method' => 'POST',
            ], 400); // or use an appropriate status code
        }

        // If no empty item_id arrays were found, return null
        return null;
    }


    /**
     *  03. Check borrowed_items if which ones are available on that date
     */
    public function getAvailableItems(array $activeItems): array
    {
        $availableItems = $activeItems;
        foreach ($activeItems as $itemGroupId => $items) {
            // If empty item ids, then continue to the next loop
            if (isset($items['item_id']) && empty($items['item_id'])) {
                // Skip the current iteration and move to the next one
                continue;
            }

            foreach ($items['item_id'] as $itemIdKey => $itemId) {
                $startDate = $items['start_date'];
                $returnDate = $items['return_date'];

                // Is the specific item available?
                $isAvailable = $this->itemAvailability->isAvailable($itemId, $startDate, $returnDate);

                if (!$isAvailable) {
                    // Delete the id that has an overlapping sched
                    unset($availableItems[$itemGroupId]['item_id'][$itemIdKey]);
                }
            }
        }
        \Log::debug("AVAILABLE ITEMS: ", ['available_items' => $availableItems]);

        return $availableItems;
    }

    /**
     *  04. Requested qty > available items on schedule (Fail)
     */
    public function checkRequestQtyAndAvailableQty(array $availableItems)
    {
        foreach ($availableItems as $key => $availableItemsPerRequest) {
            $modelName = ItemGroup::where('id', $key)->first()->model_name;
            // $availableItemIds = $availableItemsPerRequest['item_id']->toArray();
            $numberOfAvailableItems = count($availableItemsPerRequest['item_id']->toArray());
            $requestedQty = $availableItemsPerRequest['quantity'];
            if ($requestedQty > $numberOfAvailableItems) {
                $isAre = $numberOfAvailableItems > 1 ? 'are' : 'is';
                return response([
                    'status' => false,
                    'message' => 'Only ' . $numberOfAvailableItems . ' ' . $modelName . ' ' . $isAre . ' available for the selected dates',
                    'method' => "POST"
                ], 422);
            }
        }
        return false;
    }

    /**
     *  05. Requested qty < available items on schedule (SHUFFLE)
     *      DO NOTHING for Requested qty === available items on sched
     */
    public function shuffleAvailableItems($availableItems): array
    {
        $chosenItems = $availableItems;
        foreach ($chosenItems as $key => $availableItemsPerRequest) {
            $availableItemIds = $availableItemsPerRequest['item_id']->toArray();
            $numberOfAvailableItems = sizeOf($availableItemIds);
            $requestedQty = $availableItemsPerRequest['quantity'];
            /**
             *  04.2    Requested quantity < Available items on selected sched  
             *          (Randomize choosing)
             */
            if ($requestedQty < $numberOfAvailableItems) {
                /** 
                 *  -> Shuffle to randomize
                 *  -> Get the requestedQty from 0th index
                 */
                shuffle($availableItemIds);
                $chosenAvailableItemIds = array_slice($availableItemIds, 0, $requestedQty);

                $chosenItems[$key]['item_id'] = $chosenAvailableItemIds;
            }
        }
        return $chosenItems;
    }
    /**
     *  06. Insert new borrowing transaction
     */
    public function insertNewBorrowingTransaction(array $validatedData)
    {
        try {
            $transactionData = $validatedData;
            unset($transactionData['items']);

            // QUERY the purpose and department IDS
            $purposeId = BorrowPurpose::getIdByPurpose($transactionData['purpose']);
            $departmentId = Department::getIdBasedOnAcronym($transactionData['department']);
            $userDefinedPurpose = $transactionData['user_defined_purpose'];

            $user = Auth::user();
            $employeeEmail = "@apc.edu.ph";

            $newBorrowRequestArgs = null;
            // Convert APC_ID to Pahiram ID
            // Endorser is Indicated in the request
            if (isset($transactionData['endorsed_by'])) {
                $transactionData['endorsed_by'] = User::getUserIdBasedOnApcId($transactionData['endorsed_by']);
                $newBorrowRequestArgs = [
                    'endorsed_by' => $transactionData['endorsed_by'],
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

            return BorrowTransaction::create($newBorrowRequestArgs);
        } catch (\Exception $e) {
            return response([
                'status' => false,
                'message' => 'Something went wrong while adding transaction',
                'method' => "POST"
            ], 409);
        }
    }

    /**
     *  07. Insert new borrowed items
     */
    public function insertNewBorrowedItems(array $chosenItems, string $newBorrowRequestId)
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
                        'borrowing_transac_id' => $newBorrowRequestId,
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
            return response([
                'status' => false,
                'message' => 'Something went wrong while adding items',
                'method' => "POST"
            ], 409);
        }
    }


    // FOR REQUESTS WITH MULTIPLE OFFICES
    // 06. Group chosen items by office
    public function groupFinalItemListByOffice(array $finalItemList): array
    {
        $groupedItemList = [];
        foreach ($finalItemList as $itemGroupId => $chosenItems) {
            $office = ItemGroup::getOfficeById($itemGroupId);

            // Initialize the office key if not already present
            if (!isset($groupedItemList[$office])) {
                $groupedItemList[$office] = [];
            }

            // Add the chosen items under the correct office key and item group id key
            $groupedItemList[$office][$itemGroupId] = [
                ...$chosenItems
            ];
        }
        return $groupedItemList;

    }

    // Insert multiple transactions and items
    public function insertTransactionAndBorrowedItemsForMultipleOffices(
        array $validatedDataWithoutOffice,
        array $groupedFinalItemList
    ): int|Response {
        try {
            foreach ($groupedFinalItemList as $office => $chosenItem) {
                $newBorrowRequest = self::insertNewBorrowingTransaction([...$validatedDataWithoutOffice, 'department' => $office]);
                $newBorrowedItems = self::insertNewBorrowedItems($chosenItem, $newBorrowRequest->id);

                if ($newBorrowRequest instanceof Response) {
                    return $newBorrowRequest;
                }

                if ($newBorrowedItems instanceof Response) {
                    return $newBorrowedItems;
                }

                if (
                    $newBorrowedItems instanceof Response &&
                    $newBorrowRequest instanceof Response
                ) {
                    return response([
                        'status' => false,
                        'message' => 'Something went wrong while adding your request',
                        'method' => 'POST',
                    ], 500);
                }
            }
            \Log::debug("NEW Borrowed Items ", ['new_borrowed_items' => $newBorrowedItems]);
            \Log::debug("NEW Borrow Request ", ['new_borrow_req' => $newBorrowRequest]);
            return (int) count($groupedFinalItemList);

        } catch (\Exception $e) {
            return response([
                'status' => false,
                'message' => 'Something went wrong while adding your request',
                'error' => $e,
                'method' => 'POST',
            ], 500);
        }
    }

}
