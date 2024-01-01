<?php

namespace App\Services;

use App\Models\BorrowedItem;
use App\Models\BorrowedItemStatus;
use App\Models\BorrowTransaction;
use App\Models\BorrowTransactionStatus;
use App\Models\Item;
use App\Models\ItemGroup;
use App\Models\ItemStatus;
use App\Models\User;
use App\Utils\Constants\BorrowedItemStatusConst;
use App\Utils\Constants\ItemStatusConst;
use App\Services\ItemAvailability;
use Illuminate\Http\Response;

class SubmitBorrowRequestService
{
    private $maxActiveTransactions = 3;
    private $activeItemStatusCode = ItemStatusConst::ACTIVE;
    private $pendingBorrowedItemStatusCode = BorrowedItemStatusConst::PENDING;
    
    /**
     *  01. Check if user has > 3 active transactions
     */
    public function checkMaxTransactions($userId)
    {
        $activeTransactions = BorrowTransaction::where('borrower_id', $userId)->count();
        if ($activeTransactions >= $this->maxActiveTransactions) {
            return response()->json([
                'status' => false,
                'message' => 'Complete your other 3 or more transactions first.',
                'method' => 'GET'
            ], Response::HTTP_UNAUTHORIZED);
        }
        // No issue
        return false;
    }

    /**
     *  02. Get all items with active status in items tb
     *  Note: Checking if Requested Qty > Active Status Items is in Validation Requests
     */
    public function getActiveItems(array $requestedItems): array
    {
        $activeItemStatusId = ItemStatus::where('item_status_code', $this->activeItemStatusCode)->value('id');
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
        return $activeItems;
    }

    /**
     *  03. Check borrowed_items if which ones are available on that date
     */
    public function getAvailableItems(array $activeItems)
    {
        $availableItems = $activeItems;
        foreach ($activeItems as $activeItemKey => $items) {
            foreach ($items['item_id'] as $itemIdKey => $itemId) {
                $startDate = $items['start_date'];
                $returnDate = $items['return_date'];

                // Is the specific item available?
                $isAvailable = ItemAvailability::isAvailable($itemId, $startDate, $returnDate);

                if (!$isAvailable) {
                    // Delete the id that has an overlapping sched
                    unset($availableItems[$activeItemKey]['item_id'][$itemIdKey]);
                }
            }
        }
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
    public function insertNewBorrowingTransaction($validatedData, $userId)
    {
        $transactionData = $validatedData;
        unset($transactionData['items']);

        // Convert APC_ID to Pahiram ID
        if (isset($transactionData['endorsed_by'])) {
            $transactionData['endorsed_by'] = User::where('apc_id', $transactionData['endorsed_by'])->first()->id;
        }
        $newBorrowRequestArgs = [
            'borrower_id' => $userId,
            'transac_status_id' => BorrowTransactionStatus::getStatusIdByCode(1010),
            ...$transactionData
        ];
        $newBorrowRequest = BorrowTransaction::create($newBorrowRequestArgs);


        return $newBorrowRequest;
    }
    /**
     *  07. Insert new borrowed items
     */
    public function insertNewBorrowedItems($chosenItems, $newBorrowRequest)
    {
        $newBorrowedItems = [];
        foreach ($chosenItems as $borrowedItem) {
            // Remove qty field
            unset($borrowedItem['quantity']);

            foreach ($borrowedItem['item_id'] as $itemId) {
                $newBorrowedItemsArgs = [
                    'borrowing_transac_id' => $newBorrowRequest->id,
                    'item_id' => $itemId,
                    'start_date' => $borrowedItem['start_date'],
                    'due_date' => $borrowedItem['return_date'],
                    'borrowed_item_status_id' => BorrowedItemStatus::getStatusIdBasedOnCode($this->pendingBorrowedItemStatusCode)
                ];
                $newBorrowedItems[$itemId] = BorrowedItem::create($newBorrowedItemsArgs);
            }
        }
        return $newBorrowedItems;
    }

}
