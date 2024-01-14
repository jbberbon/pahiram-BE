<?php

namespace App\Http\Controllers\BorrowTransaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\BorrowTransaction\CancelBorrowRequest;
use App\Http\Requests\BorrowTransaction\EditBorrowRequest;
use App\Http\Requests\BorrowTransaction\GetBorrowRequest;
use App\Http\Requests\BorrowTransaction\SubmitBorrowRequest;
use App\Http\Resources\BorrowRequestCollection;
use App\Http\Resources\BorrowRequestResource;
use App\Models\BorrowedItem;
use App\Models\BorrowedItemStatus;
use App\Models\BorrowTransaction;
use App\Models\BorrowTransactionStatus;
use App\Models\User;
use App\Services\EditBorrowRequestService;
use App\Services\SubmitBorrowRequestService;
use App\Utils\Constants\BorrowedItemStatusConst;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManageBorrowingRequestController extends Controller
{
    protected $submitBorrowRequestService;
    protected $editBorrowRequestService;
    // private $cancelledItemStatusCode = ItemStatusConsT::CANCELLED;
    // private $maxActiveTransactions = 3;

    public function __construct(
        SubmitBorrowRequestService $submitBorrowRequestService,
        EditBorrowRequestService $editBorrowRequestService
    ) {
        $this->submitBorrowRequestService = $submitBorrowRequestService;
        $this->editBorrowRequestService = $editBorrowRequestService;
    }
    /**
     *  Display a listing of the borrow request resource.
     */
    public function index()
    {
        try {
            $user = Auth::user();
            $requestList = BorrowTransaction::where('borrower_id', $user->id)->get();

            if ($requestList->isEmpty()) {
                return response([
                    'status' => true,
                    'message' => "No Borrowing Request Sent",
                    'method' => "GET"
                ], 200);
            }

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

    public function getBorrowRequest(GetBorrowRequest $borrowRequest)
    {
        $validatedData = $borrowRequest->validated();
        try {
            $retrievedRequest = BorrowTransaction::where('id', $validatedData['borrowRequest'])->first();

            if ($retrievedRequest) {
                return response([
                    'status' => true,
                    'data' => new BorrowRequestResource($retrievedRequest),
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
                'message' => 'An error occurred while submittitng the borrow request.',
                'error' => $e->getMessage(),
                'method' => 'POST',
            ], 500);
        }
    }

    public function getOngoingRequest()
    {

    }

    /** 
     *  Submit borrowing request
     */
    public function submitBorrowRequest(SubmitBorrowRequest $borrowRequest)
    {
        $validatedData = $borrowRequest->validated();
        $userId = Auth::user()->id;

        // return $validatedData;
        // 01. Check if user has > 3 active transactions
        $maxTransactionCheck = $this->submitBorrowRequestService->checkMaxTransactions($userId);
        if ($maxTransactionCheck) {
            return $maxTransactionCheck;
        }

        // 02. Get all items with "active" status in items TB  
        $requestedItems = $validatedData['items'];
        $activeItems = $this->submitBorrowRequestService->getActiveItems($requestedItems);

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

        try {
            // 06. Insert new borrowing transaction
            $newBorrowRequest = $this->submitBorrowRequestService->insertNewBorrowingTransaction($validatedData, $userId);
            // 07. Insert new borrowed items
            $newBorrowedItems = $this->submitBorrowRequestService->insertNewBorrowedItems($chosenItems, $newBorrowRequest);

            if (!$newBorrowRequest || !$newBorrowedItems) {
                return response([
                    'status' => false,
                    'message' => 'Unable to insert new transaction and its corresponding items.',
                    'method' => 'POST',
                ], 500);
            }

            // FOR Debugging ONLY
            // return response([
            //     'status' => true,
            //     'message' => 'Successfully submitted borrow request',
            //     'borrow_request' => $newBorrowRequest,
            //     'borrowed_items' => $newBorrowedItems,
            //     'method' => 'POST',
            // ], 200);

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
     *  Edit borrow request  
     */
    public function editBorrowRequest(EditBorrowRequest $editBorrowRequest)
    {
        /**
         *  LOGIC!!
         *  01. If user does not want to edit/ add items, FINISH
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
        $requestData = $validatedData['request_data'];

        $cancelledItems = [];
        $editedItems = [];
        $addNewItems = [];
        if (isset($validatedData['add_new_items'])) {
            $addNewItems = $validatedData['add_new_items'];
        }

        $borrowRequestArgs = $this->editBorrowRequestService->prepareRequestUpdateArgs($requestData);

        // 01. User DOES NOT want to edit any borrowed item
        if (!isset($validatedData['edit_existing_items']) && !isset($validatedData['add_new_items'])) {
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
                    'message' => 'An error occurred while submittitng your request.',
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
        // $cancelQuery = $this->editBorrowRequestService->cancelQuery($cancelledItems, $requestId);
        // if (!$cancelQuery) {
        //     return response([
        //         'status' => false,
        //         'message' => 'Something went wrong while cancelling your items.',
        //         'method' => 'PATCH',
        //     ], 500);
        // }

        // 03.2 Add New Items ::: PERFORM SAME STEPS AS SUBMIT BORROW REQUEST
        $chosenNewItems = [];
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
        try {
            // Update Transaction
            $currentBorrowRequest = BorrowTransaction::findOrFail($requestId);
            $currentBorrowRequest->update($borrowRequestArgs);
        } catch (\Exception $e) {
            return response([
                'status' => false,
                'message' => 'An error occurred while editing your request data.',
                'error' => $e->getMessage(),
                'method' => 'POST',
            ], 500);
        }

        try {
            // 07. Insert new borrowed items
            $borrowRequest = BorrowTransaction::where('id', $requestId)->first();
            $newBorrowedItems = $this->submitBorrowRequestService->insertNewBorrowedItems($chosenNewItems, $borrowRequest);
            $editedBorrowedItems = $this->submitBorrowRequestService->insertNewBorrowedItems($chosenEditItems, $borrowRequest);

            if (!$editedBorrowedItems || !$newBorrowedItems) {
                return response([
                    'status' => false,
                    'message' => 'Unable to insert new transaction and its corresponding items.',
                    'method' => 'POST',
                ], 500);
            }

            // FOR Debugging ONLY
            // return response([
            //     'status' => true,
            //     'message' => 'Successfully submitted borrow request',
            //     'borrow_request' => $newBorrowRequest,
            //     'borrowed_items' => $newBorrowedItems,
            //     'method' => 'POST',
            // ], 200);

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
     *  Cancel Borrow Request
     */
    public function cancelBorrowRequest(CancelBorrowRequest $cancelBorrowRequest)
    {
        try {
            $validatedData = $cancelBorrowRequest->validated();

            $cancelledTransacStatus = BorrowTransactionStatus::where('transac_status_code', 7070)->firstOrFail();

            BorrowTransaction::where('id', $validatedData['borrowRequest'])
                ->update(['transac_status_id' => $cancelledTransacStatus->id]);

            $cancelledItemStatusCode = BorrowedItemStatusConst::CANCELLED;
            $cancelledItemStatusId = BorrowedItemStatus::where('borrowed_item_status_code', $cancelledItemStatusCode)->firstOrFail()->id;

            BorrowedItem::where('borrowing_transac_id', $validatedData['borrowRequest'])
                ->update(['borrowed_item_status_id' => $cancelledItemStatusId]);


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
