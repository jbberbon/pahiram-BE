<?php

namespace App\Http\Controllers\BorrowTransaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\BorrowTransaction\SubmitBorrowRequest;
use App\Models\BorrowedItem;
use App\Models\BorrowedItemStatus;
use App\Models\BorrowTransaction;
use App\Models\BorrowTransactionStatus;
use App\Models\Item;
use App\Models\ItemGroup;
use App\Models\ItemStatus;
use App\Models\User;
use App\Utils\Available;
use App\Utils\CheckItemAvailability;
use App\Utils\ItemAvailability;
use App\Utils\NewUserDefaultData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManageBorrowingRequestController extends Controller
{
    /**
     *  Display a listing of the borrow request resource.
     */
    public function index()
    {
        $user = Auth::user();
        $requestList = BorrowTransaction::where('borrower_id', $user->id)->get();

        if ($requestList->isEmpty()) {
            return response([
                'status' => true,
                'message' => "No Borrowing Request Sent",
                'method' => "GET"
            ], 200);
        }

        return response([
            'status' => true,
            'message' => $requestList,
            'method' => "GET"
        ], 200);
    }

    /**
     *  Submit borrowing request
     */
    public function submitBorrowRequest(SubmitBorrowRequest $borrowRequest)
    {
        $validatedData = $borrowRequest->validated();
        $user = Auth::user();

        /**
         *  01. Check if user has > 3 active transactions
         */
        $activeTransactions = BorrowTransaction::where('borrower_id', $user->id)->count();
        if ($activeTransactions >= 3) {
            return response([
                'status' => false,
                'message' => "Complete your other 3 or more transactions first.",
                'method' => "GET"
            ], 401);
        }

        /**
         *  02. Get all items with "active" status in items TB  
         *      Note: Checking if Requested Qty > Active Status Items is in Validation Requests
         */
        $activeItemStatusId = ItemStatus::where('item_status_code', 1010)->value('id');
        $requestedItems = $validatedData['items'];
        $activeItems = [];
        foreach ($requestedItems as $item) {
            $itemGroupId = $item['item_group_id'];
            $activeItems[$itemGroupId] = [
                'item_id' => Item::where('item_group_id', $itemGroupId)
                    ->where('item_status_id', $activeItemStatusId)
                    ->pluck('id'),
                'start_date' => $item['start_date'],
                'return_date' => $item['return_date'],
                'quantity' => $item['quantity']
            ];
        }

        /**
         *  03. Check for items within the item_group 
         *      if which ones are available on that date
         */
        $availableItemsForAllRequests = $activeItems;
        foreach ($activeItems as $activeItemKey => $items) {
            foreach ($items['item_id'] as $itemIdKey => $itemId) {
                $startDate = $items['start_date'];
                $returnDate = $items['return_date'];

                // Is the specific item available?
                $isAvailable = ItemAvailability::isAvailable($itemId, $startDate, $returnDate);

                if (!$isAvailable) {
                    // Delete the id that has an overlapping sched
                    unset($availableItemsForAllRequests[$activeItemKey]['item_id'][$itemIdKey]);
                }
            }
        }

        /**
         *  04. To check: 
         *      04.1 Requested qty > available items on schedule (Fail)
         *      04.2 Requested qty < available items on schedule (Success)
         *      NOTE: DO NOTHING for Requested qty === available items on sched
         */
        foreach ($availableItemsForAllRequests as $key => $availableItemsPerRequest) {
            /**
             *  04.1    Requested quantity > available items on sched
             *          *Immediately return an error response so the transaction wont proceed
             */
            $modelName = ItemGroup::where('id', $key)->first()->model_name;
            $availableItemIds = $availableItemsPerRequest['item_id']->toArray();
            $numberOfAvailableItems = sizeOf($availableItemIds);
            $requestedQty = $availableItemsPerRequest['quantity'];
            if ($requestedQty > $numberOfAvailableItems) {
                $isAre = $numberOfAvailableItems > 1 ? 'are' : 'is';
                return response([
                    'status' => false,
                    'message' => 'Only ' . $numberOfAvailableItems . ' ' . $modelName . ' ' . $isAre . ' available for the selected dates',
                    'method' => "POST"
                ], 401);
            }

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

                $availableItemsForAllRequests[$key]['item_id'] = $chosenAvailableItemIds;
            }
        }

        /**
         * 05.  Prepare default data for new row of data for Transactions TB
         */
        $transactionData = $validatedData;
        unset($transactionData['items']);
        $newBorrowRequestArgs = [
            'borrower_id' => $user->id,
            'transac_status_id' => BorrowTransactionStatus::getStatusIdByCode(1010),
            ...$transactionData
        ];
        $newBorrowRequest = BorrowTransaction::create($newBorrowRequestArgs);

        // Insert new borrowed items 
        $newBorrowedItems = [];
        foreach ($availableItemsForAllRequests as $borrowedItem) {
            // Remove qty field
            unset($borrowedItem['quantity']);

            foreach ($borrowedItem['item_id'] as $itemId) {
                $newBorrowedItemsArgs = [
                    'borrowing_transac_id' => $newBorrowRequest->id,
                    'item_id' => $itemId,
                    'start_date' => $borrowedItem['start_date'],
                    'due_date' => $borrowedItem['return_date'],
                    'borrowed_item_status_id' => BorrowedItemStatus::getStatusIdBasedOnCode(1010)
                ];
                $newBorrowedItems[$itemId] = BorrowedItem::create($newBorrowedItemsArgs);
            }
        }


        // Debugging ONLY
        // return response([
        //     'status' => true,
        //     'message' => "Borrow request submitted",
        //     'transaction_details' => $newBorrowRequest,
        //     'requested_items' => $newBorrowedItems,
        //     'method' => "POST"
        // ], 200);

        return response([
            'status' => true,
            'message' => "Borrow request submitted",
            'method' => "POST"
        ], 200);

    }

    /**
     *  Update the specified resource in storage.
     */
    public function update(Request $request, BorrowTransaction $borrowTransaction)
    {

    }

    /**
     *  Remove the specified resource from storage.
     */
    public function destroy(BorrowTransaction $borrowTransaction)
    {
        //
    }
}
