<?php

namespace App\Http\Controllers\BorrowTransaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\BorrowTransaction\CancelBorrowRequest;
use App\Http\Requests\BorrowTransaction\EditBorrowRequest;
use App\Http\Requests\BorrowTransaction\GetBorrowRequest;
use App\Http\Requests\BorrowTransaction\SubmitBorrowRequest;
use App\Http\Requests\BorrowTransaction\SubmitBorrowRequestForMultipleOfficesRequest;
use App\Http\Resources\BorrowRequestCollection;
use App\Http\Resources\BorrowRequestResource;
use App\Models\BorrowedItem;
use App\Models\BorrowTransaction;
use App\Services\RetrieveStatusService\BorrowedItemStatusService;
use App\Services\RetrieveStatusService\BorrowTransactionStatusService;
use Illuminate\Support\Facades\Auth;

use App\Services\BorrowRequestService\EditBorrowRequestService;
use App\Services\BorrowRequestService\SubmitBorrowRequestService;
use Ramsey\Uuid\Type\Integer;

class ManageBorrowingRequestController extends Controller
{
    protected $submitBorrowRequestService;
    protected $editBorrowRequestService;

    private $cancelledTransacStatusId;
    private $cancelledBorrowedItemStatusId;

    public function __construct(
        SubmitBorrowRequestService $submitBorrowRequestService,
        EditBorrowRequestService $editBorrowRequestService
    ) {
        $this->submitBorrowRequestService = $submitBorrowRequestService;
        $this->editBorrowRequestService = $editBorrowRequestService;

        $this->cancelledTransacStatusId = BorrowTransactionStatusService::getCancelledTransactionId();
        $this->cancelledBorrowedItemStatusId = BorrowedItemStatusService::getCancelledStatusId();
    }

    /**
     *  Display a listing of the borrow request resource.
     */
    public function index()
    {
        try {
            $userId = Auth::id();
            $requestList = BorrowTransaction::where('borrower_id', $userId)->get();

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
            $retrievedRequest = BorrowTransaction::where('id', $validatedData['borrowRequest'])->first();


            if ($retrievedRequest) {
                $transactionDetails = new BorrowRequestResource($retrievedRequest);

                $items = BorrowedItem::where('borrowing_transac_id', $validatedData['borrowRequest'])
                    ->join('items', 'borrowed_items.item_id', '=', 'items.id')
                    ->join('item_groups', 'items.item_group_id', '=', 'item_groups.id')
                    ->join(
                        'borrowed_item_statuses',
                        'borrowed_items.borrowed_item_status_id',
                        '=',
                        'borrowed_item_statuses.id'
                    )
                    ->groupBy(
                        'item_groups.model_name',
                        'item_groups.id',
                        'borrowed_items.start_date',
                        'borrowed_items.due_date',
                        'borrowed_items.borrowed_item_status_id',
                        'borrowed_item_statuses.borrowed_item_status'
                    )
                    ->select(
                        'item_groups.id',
                        'item_groups.model_name',
                        \DB::raw('COUNT(borrowed_items.id) as quantity'),
                        'borrowed_items.start_date',
                        'borrowed_items.due_date',
                        'borrowed_item_statuses.borrowed_item_status'
                    )
                    ->get();

                // // Restructure the $items array for front end but already fixed
                // $restructuredItems = $items
                //     ->map(function ($item) {
                //         return [
                //             'item' => [
                //                 'model_name' => $item->model_name,
                //                 'id' => $item->id,
                //             ],
                //             'quantity' => $item->quantity,
                //             'start_date' => $item->start_date,
                //             'due_date' => $item->due_date,
                //             'borrowed_item_status' => $item->borrowed_item_status,
                //         ];

                //     });

                return response([
                    'status' => true,
                    'data' => [
                        'transac_data' => $transactionDetails,
                        'items' => $items,
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
     *  Submit borrowing request
     */
    public function submitBorrowRequest(SubmitBorrowRequest $borrowRequest)
    {
        try {
            /**
             * LOGIC!!
             * 01. Check if user has > 3 ACTIVE, ONGOING, OVERDUE  transactions
             * 02. Get all items with "active" status in the items table.
             * 03. Check the borrowed_items table to determine which items are available on the specified date.
             * 04. If the requested quantity is greater than the available quantity, fail.
             * 05. If the requested quantity is less than the available quantity, shuffle and choose items.
             * 06. Insert a new borrowing transaction.
             * 07. Insert new borrowed items.
             * 08. If successful, return a success response; otherwise, return an error response.
             */
            $validatedData = $borrowRequest->validated();
            $userId = Auth::id();

            // 01. Check if user has > 3 ACTIVE, ONGOING, OVERDUE  transactions
            $maxTransactionCheck = $this->submitBorrowRequestService->checkMaxTransactions($userId);
            if ($maxTransactionCheck) {
                return $maxTransactionCheck;
            }

            // 02. Get all items with "active" status in items TB  
            $requestedItems = $validatedData['items'];
            $activeItems = $this->submitBorrowRequestService->getActiveItems($requestedItems);

            // 02.1. EDGE CASE: Check for empty active item_id array in activeItems variable
            $emptyActiveItemsIdArray = $this->submitBorrowRequestService->checkActiveItemsForEmptyItemIdField($activeItems);
            if ($emptyActiveItemsIdArray !== null) {
                return $emptyActiveItemsIdArray;
            }

            // 03. Check borrowed_items if which ones are available on that date
            $availableItems = $this->submitBorrowRequestService->getAvailableItems($activeItems);

            // 04. Requested qty > available items on schedule (Fail)
            $isRequestQtyMoreThanAvailableQty = $this->submitBorrowRequestService
                ->checkRequestQtyAndAvailableQty($availableItems);
            if ($isRequestQtyMoreThanAvailableQty) {
                return $isRequestQtyMoreThanAvailableQty;
            }

            // 05. Requested qty < available items on schedule (SHUFFLE then Choose)
            $chosenItems = $this->submitBorrowRequestService->shuffleAvailableItems($availableItems);

            // 06. Insert new borrowing transaction
            $newBorrowRequest = $this->submitBorrowRequestService->insertNewBorrowingTransaction($validatedData);

            // 07. Insert new borrowed items
            $newBorrowedItems = $this->submitBorrowRequestService->insertNewBorrowedItems($chosenItems, $newBorrowRequest->id);

            if (!$newBorrowRequest || !$newBorrowedItems) {
                return response([
                    'status' => false,
                    'message' => 'Unable to insert new transaction and its corresponding items.',
                    'method' => 'POST',
                ], 500);
            }
            return response([
                'status' => true,
                'message' => 'Successfully submitted borrow request',
                'method' => 'POST',
            ], 200);

        } catch (\Exception $e) {
            return response([
                'status' => false,
                'message' => 'An error occurred while submittitng your request.',
                'error' => $e->getMessage(),
                'method' => 'POST',
            ], 500);
        }
    }
    /**
     *  Submit Request V2
     */
    public function submitBorrowRequestV2(SubmitBorrowRequestForMultipleOfficesRequest $borrowRequest)
    {
        try {
            $validatedData = $borrowRequest->validated();
            $userId = Auth::id();

            // 01. Check if user has > 3 ACTIVE, ONGOING, OVERDUE  transactions
            $maxTransactionCheck = $this
                ->submitBorrowRequestService
                ->checkMaxTransactions($userId);

            if ($maxTransactionCheck) {
                return $maxTransactionCheck;
            }

            // 02. Get all items with "active" status in items TB  
            $requestedItems = $validatedData['items'];
            $activeItems = $this
                ->submitBorrowRequestService
                ->getActiveItems($requestedItems);

            // 02.1. EDGE CASE: Check for empty active item_id array in activeItems variable
            $emptyActiveItemsIdArray = $this
                ->submitBorrowRequestService
                ->checkActiveItemsForEmptyItemIdField($activeItems);

            if ($emptyActiveItemsIdArray !== null) {
                return $emptyActiveItemsIdArray;
            }

            // 03. Check borrowed_items if which ones are available on that date
            $availableItems = $this
                ->submitBorrowRequestService
                ->getAvailableItems($activeItems);

            // 04. Requested qty > available items on schedule (Fail)
            $isRequestQtyMoreThanAvailableQty = $this
                ->submitBorrowRequestService
                ->checkRequestQtyAndAvailableQty($availableItems);

            if ($isRequestQtyMoreThanAvailableQty) {
                return $isRequestQtyMoreThanAvailableQty;
            }

            // 05. Requested qty < available items on schedule (SHUFFLE then Choose)
            $chosenItems = $this
                ->submitBorrowRequestService
                ->shuffleAvailableItems($availableItems);

            // 06. Group chosen items by office
            $groupedFinalItemList = $this
                ->submitBorrowRequestService
                ->groupFinalItemListByOffice($chosenItems);

            // 07. Insert transaction and Borrowed Items
            $newTransactionsCount = $this
                ->submitBorrowRequestService
                ->insertTransactionAndBorrowedItemsForMultipleOffices(
                    $validatedData,
                    $groupedFinalItemList
                );


            // 08. Check if success
            if ($newTransactionsCount instanceof Integer) {
                return response([
                    'status' => true,
                    'message' => 'Successfully submitted ' . $newTransactionsCount . ' borrow request',
                    'method' => 'POST',
                ], 200);
            }

            if ($newTransactionsCount instanceof Response) {
                return $newTransactionsCount;
            }
        } catch (\Exception $e) {
            return response([
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
         *  LOGIC!!
         *  01. If user does not want to edit/ add items, FINISH
         *  01.1 If w/ Add new Items but no edit existing items
         *  02. Segregate edit_existing_items into TWO :: with is_cancelled && without is_cancelled
         *  03. Prepare Data for Querying for each
         *      03.1 Cancel Items
         *      03.2 Add New Items
         *      03.3 Edit Items
         *  04. Query DB with final data for each
         *      04.1 Request Data
         *      04.2 Cancel Items
         *      04.3 Add New Items
         *      04.4 Edit Itemss
         */
        $validatedData = $editBorrowRequest->validated();
        $requestId = $validatedData['requestId'];
        $requestData = null;
        // $requestData = $validatedData['request_data'];

        $cancelledItems = [];
        $editedItems = [];
        $addNewItems = [];
        $borrowRequestArgs = null;
        $chosenNewItems = [];

        // Check if Request_Data field is provided 
        if (isset($validatedData['request_data'])) {
            $requestData = $validatedData['request_data'];
            // Prepare Transaction Data Payload for DB UPDATE QUERY
            $borrowRequestArgs = $this->editBorrowRequestService->prepareRequestUpdateArgs($requestData);
        }

        if (isset($validatedData['add_new_items'])) {
            $addNewItems = $validatedData['add_new_items'];

            // 03.2 Add New Items ::: PERFORM SAME STEPS AS SUBMIT BORROW REQUEST
            if (count($addNewItems) > 0) {
                // 03.2.1 Get all items with "active" status in items TB 
                $activeItems = $this->submitBorrowRequestService->getActiveItems($addNewItems);

                // 03.2.2 Check borrowed_items if which ones are available on that date
                $availableItems = $this->submitBorrowRequestService->getAvailableItems($activeItems);

                // 03.2.3 Requested qty > available items on schedule (Fail)
                $isRequestQtyMoreThanAvailableQty = $this->submitBorrowRequestService->checkRequestQtyAndAvailableQty($availableItems);
                if ($isRequestQtyMoreThanAvailableQty) {
                    return $isRequestQtyMoreThanAvailableQty;
                }

                // 03.2.4 Requested qty < available items on schedule (SHUFFLE then Choose)
                $chosenNewItems = $this->submitBorrowRequestService->shuffleAvailableItems($availableItems);
            }

        }

        // 01. User DOES NOT want to edit or add items
        if (
            isset($validatedData['request_data']) &&
            !isset($validatedData['edit_existing_items']) &&
            !isset(
            $validatedData['add_new_items']
        )
        ) {
            try {
                // Update Transaction
                $currentBorrowRequest = BorrowTransaction::findOrFail($requestId);
                $currentBorrowRequest->update($borrowRequestArgs);
                return response([
                    'status' => true,
                    'message' => 'Successfully edited borrow request',
                    'method' => 'PATCH',
                ], 200);
            } catch (\Exception $e) {
                return response([
                    'status' => false,
                    'message' => 'Transaction doesn`t exist based on ID',
                    'error' => $e->getMessage(),
                    'method' => 'POST',
                ], 500);
            }
        }

        // 01.01 Edit request data and Add items
        if (
            isset($validatedData['request_data']) &&
            !isset($validatedData['edit_existing_items']) &&
            isset(
            $validatedData['add_new_items']
        )
        ) {
            try {
                // Update Transaction
                $currentBorrowRequest = BorrowTransaction::findOrFail($requestId);
                $currentBorrowRequest->update($borrowRequestArgs);

                // Add new items
                if (count($chosenNewItems) > 0) {
                    $newBorrowedItems = $this->submitBorrowRequestService->insertNewBorrowedItems($chosenNewItems, $currentBorrowRequest->id);
                }

                return response([
                    'status' => true,
                    'message' => 'Successfully edited borrow request',
                    'method' => 'PATCH',
                ], 200);
            } catch (\Exception $e) {
                return response([
                    'status' => false,
                    'message' => 'Transaction doesn`t exist based on ID',
                    'error' => $e->getMessage(),
                    'method' => 'POST',
                ], 500);
            }
        }

        // 01.02 Add items ONLY
        if (
            !isset($validatedData['request_data']) &&
            !isset($validatedData['edit_existing_items']) &&
            isset(
            $validatedData['add_new_items']
        )
        ) {
            try {
                $currentBorrowRequest = BorrowTransaction::findOrFail($requestId);

                // Add new items
                if (count($chosenNewItems) > 0) {
                    $newBorrowedItems = $this->submitBorrowRequestService->insertNewBorrowedItems($chosenNewItems, $currentBorrowRequest->id);
                }

                return response([
                    'status' => true,
                    'message' => 'Successfully edited borrow request',
                    'method' => 'PATCH',
                ], 200);
            } catch (\Exception $e) {
                return response([
                    'status' => false,
                    'message' => 'Transaction doesn`t exist based on ID',
                    'error' => $e->getMessage(),
                    'method' => 'POST',
                ], 500);
            }
        }



        // 02. Segregate edit_existing_items into TWO :: with is_cancelled && without is_cancelled
        $editExistingItems = $validatedData['edit_existing_items'];
        foreach ($editExistingItems as $currentExistingItem) {
            $currentExistingItemId = $currentExistingItem['item_group_id'];

            // 02.1 With is_cancelled
            $cancelledGroupId = $this->editBorrowRequestService->isCancelled($currentExistingItem);
            if ($cancelledGroupId) {
                array_push($cancelledItems, $cancelledGroupId);
                continue; // Skip to the next iteration
            }

            // 02.2 Without is_cancelled
            $editedItems[$currentExistingItemId] = [...$currentExistingItem];
        }

        // 03. Prepare Data for Querying for each
        // 03.1 Cancel Items
        $cancelQuery = $this->editBorrowRequestService->cancelQuery($cancelledItems, $requestId);
        if (!$cancelQuery) {
            return response([
                'status' => false,
                'message' => 'Something went wrong while cancelling your items.',
                'method' => 'PATCH',
            ], 500);
        }

        // // 03.2 Add New Items ::: PERFORM SAME STEPS AS SUBMIT BORROW REQUEST
        // $chosenNewItems = [];
        // if (count($addNewItems) > 0) {
        //     // 03.2.1 Get all items with "active" status in items TB 
        //     $activeItems = $this->submitBorrowRequestService->getActiveItems($addNewItems);

        //     // 03.2.2 Check borrowed_items if which ones are available on that date
        //     $availableItems = $this->submitBorrowRequestService->getAvailableItems($activeItems);

        //     // 03.2.3 Requested qty > available items on schedule (Fail)
        //     $isRequestQtyMoreThanAvailableQty = $this->submitBorrowRequestService->checkRequestQtyAndAvailableQty($availableItems);
        //     if ($isRequestQtyMoreThanAvailableQty) {
        //         return $isRequestQtyMoreThanAvailableQty;
        //     }

        //     // 03.2.4 Requested qty < available items on schedule (SHUFFLE then Choose)
        //     $chosenNewItems = $this->submitBorrowRequestService->shuffleAvailableItems($availableItems);
        // }

        // 03.3 Edit Items
        $editedItemsGroupId = array_column($editedItems, 'item_group_id');
        // 03.3.1 Perform same Logic sequence as Cancel
        $cancelQuery = [];
        if (count($editedItemsGroupId) > 0) {
            // Same Logic Sequence as Cancel
            $cancelQuery = $this->editBorrowRequestService->cancelQuery($editedItemsGroupId, $requestId);
            if (!$cancelQuery) {
                return response([
                    'status' => false,
                    'message' => 'Something went wrong while cancelling your items.',
                    'method' => 'PATCH',
                ], 500);
            }
        }

        // 03.3.2 Get the additional data in preparation for adding items 
        foreach ($editedItems as $editedItemKey => $editedItem) {
            // Convert to array first
            $cancelQueryArray = json_decode(json_encode($cancelQuery), true);
            // 03.3.2.1 If the updated data is QTY ONLY
            if (isset($editedItem['quantity']) && !isset($editedItem['start_date']) && !isset($editedItem['return_date'])) {
                // Get start and due date from A RECENTLY cancelled item
                $retrievedStartDate = $cancelQueryArray[$editedItemKey][0]['start_date'];  // 0th index as all items have same dates
                $retrievedReturnDate = $cancelQueryArray[$editedItemKey][0]['due_date']; // 0th index as all items have same dates

                $editedItems[$editedItemKey] = [
                    ...$editedItem,
                    'start_date' => $retrievedStartDate,
                    'return_date' => $retrievedReturnDate
                ];
            }
            //  03.3.2.2 If the updated data wants to EDIT DATES but no QTY field
            if (isset($editedItem['start_date']) && isset($editedItem['return_date']) && !isset($editedItem['quantity'])) {
                $retrievedQuantity = count($cancelQueryArray[$editedItemKey]);

                $editedItems[$editedItemKey] = [
                    ...$editedItem,
                    'quantity' => $retrievedQuantity
                ];

            }
            // If all fields are being updated
            // DO NOTHING as all fields are already present
        }

        // 03.3.3 PERFORM SAME STEPS AS SUBMIT BORROW REQUEST
        $chosenEditItems = [];
        if (count($editedItems) > 0) {
            // 03.2.1 Get all items with "active" status in items TB 
            $activeItems = $this->submitBorrowRequestService->getActiveItems($editedItems);

            // 03.2.2 Check borrowed_items if which ones are available on that date
            $availableItems = $this->submitBorrowRequestService->getAvailableItems($activeItems);

            // 03.2.3 Requested qty > available items on schedule (Fail)
            $isRequestQtyMoreThanAvailableQty = $this->submitBorrowRequestService->checkRequestQtyAndAvailableQty($availableItems);
            if ($isRequestQtyMoreThanAvailableQty) {
                return $isRequestQtyMoreThanAvailableQty;
            }

            // 03.2.4 Requested qty < available items on schedule (SHUFFLE then Choose)
            $chosenEditItems = $this->submitBorrowRequestService->shuffleAvailableItems($availableItems);
        }

        // 04. Query DB using final data for each part
        // 04.1 Request Data
        if ($requestData && $borrowRequestArgs) {
            try {
                // Update Transaction
                $currentBorrowRequest = BorrowTransaction::findOrFail($requestId);
                $currentBorrowRequest->update($borrowRequestArgs);
            } catch (\Exception $e) {
                return response([
                    'status' => false,
                    'message' => 'Couldn`t find transaction based on given ID',
                    'error' => $e->getMessage(),
                    'method' => 'POST',
                ], 500);
            }
        }

        // 07. Insert new borrowed items
        try {
            $borrowRequest = BorrowTransaction::findOrFail($requestId);
            $newBorrowedItems = null;
            $editedBorrowedItems = null;

            if (count($chosenNewItems) > 0) {
                $newBorrowedItems = $this->submitBorrowRequestService->insertNewBorrowedItems($chosenNewItems, $borrowRequest->id);
            }

            if (count($chosenEditItems) > 0) {
                $editedBorrowedItems = $this->submitBorrowRequestService->insertNewBorrowedItems($chosenEditItems, $borrowRequest->id);
            }

            return response([
                'status' => true,
                'message' => 'Successfully edited borrow request',
                'method' => 'POST',
            ], 200);

        } catch (\Exception $e) {
            return response([
                'status' => false,
                'message' => 'An error occurred while submittitng your request.',
                'error' => $e->getMessage(),
                'method' => 'POST',
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
