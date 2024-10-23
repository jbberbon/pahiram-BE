<?php

namespace App\Http\Controllers\BorrowTransaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\BorrowTransaction\CancelBorrowRequest;
use App\Http\Requests\BorrowTransaction\EditBorrowRequest;
use App\Http\Requests\BorrowTransaction\GetBorrowRequest;
use App\Http\Requests\BorrowTransaction\SubmitBorrowRequestForMultipleOfficesRequest;
use App\Http\Resources\BorrowRequestCollection;
use App\Http\Resources\BorrowRequestResource;
use App\Models\BorrowedItem;
use App\Models\BorrowTransaction;
use App\Services\BorrowRequestService\BorrowRequestFinalizationService;
use App\Services\RetrieveStatusService\BorrowedItemStatusService;
use App\Services\RetrieveStatusService\BorrowTransactionStatusService;
use App\Services\UserService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Services\BorrowRequestService\EditBorrowRequestService;
use App\Services\BorrowRequestService\BorrowRequestHelperService;

class ManageBorrowingRequestController extends Controller
{
    protected $borrowRequestHelperService;
    protected $editBorrowRequestService;
    protected $borrowRequestFinalizationService;

    private $cancelledTransacStatusId;
    private $cancelledBorrowedItemStatusId;

    private $userService;

    public function __construct(
        BorrowRequestHelperService $borrowRequestHelperService,
        EditBorrowRequestService $editBorrowRequestService,
        UserService $userService,
        BorrowRequestFinalizationService $borrowRequestFinalizationService,
    ) {
        $this->borrowRequestHelperService = $borrowRequestHelperService;
        $this->editBorrowRequestService = $editBorrowRequestService;
        $this->borrowRequestFinalizationService = $borrowRequestFinalizationService;

        $this->cancelledTransacStatusId = BorrowTransactionStatusService::getCancelledTransactionId();
        $this->cancelledBorrowedItemStatusId = BorrowedItemStatusService::getCancelledStatusId();

        $this->userService = $userService;
    }

    /**
     *  Display a listing of the borrow request resource.
     */
    public function index()
    {
        try {
            $userId = Auth::id();
            $requestList = BorrowTransaction::where('borrower_id', $userId)
                ->paginate(21);

            $requestCollection = new BorrowRequestCollection($requestList);
            return response([
                'status' => true,
                'data' => $requestCollection,
                'method' => "GET"
            ], 200);
        } catch (\Exception $e) {
            return response([
                'status' => false,
                'message' => 'An error occurred while fetching borrowing requests',
                'error' => $e->getMessage(),
                'method' => 'GET',
            ], 500);
        }
    }

    /**
     *  Display borrow request resource details
     */
    public function getBorrowRequest(GetBorrowRequest $borrowRequest)
    {
        $validatedData = $borrowRequest->validated();
    
        try {
            // Retrieve the borrow request transaction by its ID with the borrower relationship
            $retrievedRequest = BorrowTransaction::with('borrower')
                ->where('id', $validatedData['borrowRequest'])
                ->first();
    
            if ($retrievedRequest) {
                // Transform transaction details using the BorrowRequestResource
                $transactionDetails = new BorrowRequestResource($retrievedRequest);
    
                // Get the apc_id from the borrower relationship
                $apcId = $retrievedRequest->borrower ? $retrievedRequest->borrower->apc_id : null;
    
                // Fetch individual borrowed items associated with the borrow transaction,
                // excluding those with a status of "CANCELLED"
                $items = BorrowedItem::where('borrowing_transac_id', $validatedData['borrowRequest'])
                    ->join('items', 'borrowed_items.item_id', '=', 'items.id')
                    ->join('item_groups', 'items.item_group_id', '=', 'item_groups.id')
                    ->join( // Use leftJoin to allow for null statuses
                        'borrowed_item_statuses',
                        'borrowed_items.borrowed_item_status_id',
                        '=',
                        'borrowed_item_statuses.id'
                    )
                    ->select(
                        'borrowed_items.id as borrowed_item_id',
                        'item_groups.id as item_group_id',
                        'item_groups.model_name',
                        'borrowed_items.start_date',
                        'borrowed_items.due_date',
                        'borrowed_item_statuses.borrowed_item_status',
                        'borrowed_item_statuses.id as borrowed_item_status_id',
                        'items.apc_item_id as apc_item_id',
                    )
                    ->where('borrowed_item_statuses.borrowed_item_status', '!=', 'CANCELLED') // Exclude CANCELLED status
                    ->get();  
    
                return response([
                    'status' => true,
                    'data' => [
                        'transac_data' => $transactionDetails,
                        'items' => $items, // Use restructured items with detailed statuses
                    ],
                    'method' => 'GET',
                ], 200);
            } else {
                return response([
                    'status' => false,
                    'message' => 'Borrow request not found.',
                    'method' => 'GET',
                ], 404);
            }
        } catch (\Exception $e) {
            return response([
                'status' => false,
                'message' => 'An error occurred while fetching the borrow request.',
                'error' => $e->getMessage(),
                'method' => 'POST',
            ], 500);
        }
    }
    
    

    
    

    
    

    /**
     *  Submit Request V2
     */
    public function submitBorrowRequestV2(
        SubmitBorrowRequestForMultipleOfficesRequest $borrowRequest
    ) {
        /* 
         *  LOGIC FLOW
         *  01. Import Endorser Account to Pahiram if it does not exist yet
         *  02. Check if user has >= 3 ACTIVE, ONGOING, OVERDUE  transactions
         *  03. Process requested items and create a final list
         *  04. Group chosen items by office
         *  05. Insert transaction/s and borrowed items
         */
        try {
            DB::beginTransaction();
            $validatedData = $borrowRequest->validated();
            $userId = Auth::id();

            // 01. Import Endorser Account to Pahiram if it does not exist yet
            if (isset($validatedData['endorsed_by'])) {
                $res = $this
                    ->userService
                    ->handleRetrieveUserDataFromApcisWithoutLogin(
                        $validatedData['endorsed_by'],
                        $validatedData['apcis_token']
                    );

                if ($res !== null) {
                    return response()->json($res, 500);
                }
            }

            // 02. Check if user has >= 3 ACTIVE, ONGOING, OVERDUE  transactions
            $maxTransactionCheck = $this
                ->borrowRequestHelperService
                ->checkMaxTransactions($userId);

            if ($maxTransactionCheck) {
                DB::rollBack();
                return response()->json(
                    $maxTransactionCheck,
                    401
                );
            }
            // 03. Prepare transaction data
            $preparedTransacData = $this
                ->borrowRequestHelperService
                ->prepareRequestArgs($validatedData);

            // 03. Process requested items and create a final list
            $requestedItems = $validatedData['items'];
            $chosenItemsList = $this
                ->borrowRequestFinalizationService
                ->processRequestedItems(
                    requestedItems: $requestedItems
                );
            if (isset($finalItemList['status'])) {
                DB::rollBack();
                return response()->json(
                    $chosenItemsList,
                    500
                );
            }

            // 04. Group chosen items by office
            $groupedItemsByOffice = $this
                ->borrowRequestHelperService
                ->groupFinalItemListByOffice(finalItemList: $chosenItemsList);

            // 05. Insert transaction/s and borrowed items
            $newTransactionsCount = $this
                ->borrowRequestFinalizationService
                ->insertTransactionAndBorrowedItemsForMultipleOffices(
                    validatedDataWithoutOffice: $preparedTransacData,
                    groupedFinalItemList: $groupedItemsByOffice
                );

            // Success
            if (is_int($newTransactionsCount) && $newTransactionsCount > 0) {
                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'Successfully submitted ' . $newTransactionsCount . ' borrow request',
                    'method' => 'POST',
                ], 200);
            }

            // Fail
            DB::rollBack();
            if (is_array($newTransactionsCount)) {
                return response()->json(
                    [$newTransactionsCount],
                    409
                );
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while submittitng your request',
                'error' => $e->getMessage(),
                'method' => 'POST',
            ], 500);
        }
    }

    /**
     *  Edit borrow request  
     */
    public function editBorrowRequest(EditBorrowRequest $editBorrowRequest)
    {
        /**
         *  New Logic
         *  01. If request_data is provided, update it instantly
         *  02. If edit_existing_items is provided
         *      --> 02.01.  Segregate the edit_existing_items
         *  03. Perform Cancel items with determined ItemGroup Ids
         *  04. Perform Cancel items with determined BorrowedItem Ids
         *  05. Add new items OR update the items that needs to be date changed
         *      --> 05.01. Process requested items and create a final list
         *      --> 05.02. Insert chosen items
         */
        try {
            DB::beginTransaction();
            $validatedData = $editBorrowRequest->validated();
            $requestId = $validatedData['requestId'];

            $requestData = null;
            $requestedItems = isset($validatedData['add_new_items']) ? $validatedData['add_new_items'] : [];
            $borrowRequestArgs = null;

            // 01. If request_data is provided, update it instantly
            if (isset($validatedData['request_data'])) {
                $requestData = $validatedData['request_data'];

                // Check if Endorser is indicated
                if (isset($requestData['endorsed_by'])) {
                    $res = $this
                        ->userService
                        ->handleRetrieveUserDataFromApcisWithoutLogin(
                            endorserApcId: $requestData['endorsed_by'],
                            apcisToken: $requestData['apcis_token']
                        );

                    if ($res !== null) {
                        return response()->json($res, 500);
                    }
                }

                // Prepare Transaction Data Payload for DB UPDATE QUERY
                $borrowRequestArgs = $this
                    ->editBorrowRequestService
                    ->prepareRequestUpdateArgs($requestData);

                // Update Transac Data
                $currentBorrowRequest = BorrowTransaction::findOrFail($requestId);
                $currentBorrowRequest->update($borrowRequestArgs);
            }


            $toBeCancelledItemGroupIds = [];
            $toBeCancelledBorrowedItemIds = [];
            $itemsToBeAdded = $requestedItems;
            $toBeEdited = [];

            // 02. edit_existing_items field is provided. process the data
            if (isset($validatedData['edit_existing_items'])) {
                try {
                    // 02.01.  Segregate the edit_existing_items
                    $segregated = $this
                        ->editBorrowRequestService
                        ->segregateToBeEditedItems($validatedData['edit_existing_items']);

                    $toBeEdited = $segregated['toBeEdited'];
                    // Pluck the item Group id, and merge it to toBeCancelledItemGroupIds
                    $toBeEditedIds = array_column(
                        array: $toBeEdited,
                        column_key: 'item_group_id'
                    );

                    // Store all the ids that need to be cancelled
                    $toBeCancelledItemGroupIds = array_merge(
                        $segregated['toBeCancelledIds'],
                        $toBeEditedIds
                    );

                    // This will be segregated further
                    $toBeQtyChangedOnly = $segregated['toBeQtyChangedOnly'];

                    // Initialize the variable to a default value (could be null or an empty array)
                    $processedToBeChangedQty = [
                        'toBeCancelledBorrowedItemIds' => [],
                        'itemsToBeAdded' => []
                    ];

                    // Process toBeQtyChangedOnly
                    if (count($toBeQtyChangedOnly) > 0) {
                        $processedToBeChangedQty = $this
                            ->editBorrowRequestService
                            ->processToBeQtyChangedOnly(
                                transacId: $requestId,
                                toBeQtyChangedOnly: $toBeQtyChangedOnly
                            );
                    }

                    // --> Update the array value
                    if (count($processedToBeChangedQty['toBeCancelledBorrowedItemIds']) > 0) {
                        $toBeCancelledBorrowedItemIds = array_unique(
                            array_merge(
                                $toBeCancelledBorrowedItemIds,
                                $processedToBeChangedQty['toBeCancelledBorrowedItemIds']
                            )
                        );
                    }
                    // --> Update the array value
                    if (count($processedToBeChangedQty['itemsToBeAdded']) > 0) {
                        $itemsToBeAdded = array_merge(
                            $itemsToBeAdded,
                            $processedToBeChangedQty['itemsToBeAdded']
                        );
                    }
                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => 'Failed to edit items.',
                        'error' => $e->getMessage(),
                        'method' => 'POST',
                    ], 500);
                }
            }

            // 03. Perform Cancel items with determined ItemGroup Ids
            if (count($toBeCancelledItemGroupIds) > 0) {
                BorrowedItem::where('borrowing_transac_id', $requestId)
                    ->join('items', 'borrowed_items.item_id', '=', 'items.id')
                    ->join('item_groups', 'items.item_group_id', '=', 'item_groups.id')
                    ->whereIn('item_groups.id', $toBeCancelledItemGroupIds) // Filter by item group ID
                    ->update([
                        'borrowed_items.borrowed_item_status_id' => $this->cancelledBorrowedItemStatusId
                    ]);
            }

            // 04. Perform Cancel items with determined BorrowedItem Ids
            if (count($toBeCancelledBorrowedItemIds) > 0) {
                BorrowedItem::where('borrowing_transac_id', $requestId)
                    ->whereIn('id', $toBeCancelledBorrowedItemIds)
                    ->update([
                        'borrowed_item_status_id' => $this->cancelledBorrowedItemStatusId
                    ]);
            }

            // 05. Add new items OR update the items that needs to be date changed. 
            //      --> This means we will go through what Submit request does
            if (count($toBeEdited) > 0 || count($itemsToBeAdded) > 0) {
                $mergedItems = array_merge($toBeEdited, $itemsToBeAdded);
                // 05.01. Process requested items and create a final list
                $chosenItemList = $this
                    ->borrowRequestFinalizationService
                    ->processRequestedItems(
                        requestedItems: $mergedItems,
                    );


                if (isset($finalItemList['status'])) {
                    DB::rollBack();
                    return response()->json(
                        $chosenItemList,
                        500
                    );
                }

                // 05.02. Insert chosen items 
                $insertItems = $this->borrowRequestFinalizationService
                    ->insertNewBorrowedItems(
                        chosenItems: $chosenItemList,
                        borrowRequestId: $requestId
                    );

                if (isset($finalItemList['status'])) {
                    DB::rollBack();
                    return response()->json(
                        $insertItems,
                        500
                    );
                }
            }
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Successfully edited your borrow request.',
                'method' => 'PATCH'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while editing you request.',
                'method' => 'PATCH',
                'error' => $e->getMessage(),
            ], 500);
        }

    }

    /**
     *  Cancel Borrow Request
     */
    public function cancelBorrowRequest(CancelBorrowRequest $cancelBorrowRequest)
    {
        try {
            $validatedData = $cancelBorrowRequest->validated();

            $transaction = BorrowTransaction::findOrFail($validatedData['borrowRequest']);
            $transaction->update(['transac_status_id' => $this->cancelledTransacStatusId]);

            // Cancel the BORROWED ITEMS too
            BorrowedItem::where('borrowing_transac_id', $validatedData['borrowRequest'])
                ->update(['borrowed_item_status_id' => $this->cancelledBorrowedItemStatusId]);

            return response([
                'status' => true,
                'message' => 'Successfully cancelled borrow request.',
                'method' => 'PATCH'
            ], 200);
        } catch (\Exception $e) {
            return response([
                'status' => false,
                'message' => 'An error occurred.',
                'method' => 'PATCH',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
