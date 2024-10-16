<?php

namespace App\Services\BorrowRequestService;

use App\Models\BorrowPurpose;
use App\Models\BorrowTransaction;
use App\Models\Item;
use App\Models\ItemGroup;
use App\Models\User;
use App\Services\RetrieveStatusService\ItemStatusService;
use App\Services\ItemAvailability;
use App\Services\RetrieveStatusService\BorrowTransactionStatusService;

class BorrowRequestHelperService
{
    const MAX_ACTIVE_TRANSACTIONS = 4;
    private $itemAvailability;
    private $activeItemStatusId;
    private $activeTransactionStatusIds;

    public function __construct(ItemAvailability $itemAvailability)
    {
        $this->itemAvailability = $itemAvailability;

        // Item Statuses
        $this->activeItemStatusId = ItemStatusService::getActiveStatusId();

        $this->activeTransactionStatusIds = BorrowTransactionStatusService::getActiveTransactionIds();
    }

    public function prepareRequestArgs($validatedData)
    {
        // Retrieve "transaction" (item data not included) related details and patch it to tb
        if (isset($validatedData['endorsed_by'])) {
            $validatedData['endorsed_by'] = User::getUserIdBasedOnApcId($validatedData['endorsed_by']);
        }

        if (isset($validatedData['purpose'])) {
            // Change Purpose to Purpose_id 
            $validatedData['purpose_id'] = BorrowPurpose::getIdByPurpose($validatedData['purpose']);
            // Delete the original purpose request field as it is no longer needed
            unset($validatedData['purpose']);
        }

        if (isset($validatedData['apcis_token'])) {
            // Remove APCIS Token as it is not part of the Transaction Table
            unset($validatedData['apcis_token']);
        }

        if (isset($validatedData['items'])) {
            unset($validatedData['items']);
        }

        return $validatedData;
    }
    /**
     *  01. Check if user has > 3 active transactions
     */
    public function checkMaxTransactions($userId): null|array
    {
        $activeTransactions = BorrowTransaction::where('borrower_id', $userId)
            ->whereIn('transac_status_id', $this->activeTransactionStatusIds)
            ->count();

        if ($activeTransactions >= self::MAX_ACTIVE_TRANSACTIONS) {
            return [
                'status' => false,
                'message' => 'Complete your other 3 or more transactions first.',
                'method' => 'GET'
            ];
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
    public function checkActiveItemsForEmptyItemIdField(array $activeItems): null|array
    {
        $errorArray = [
            'status' => false,
            'message' => 'Some item groups have an empty item_id array.',
            'method' => 'POST',
        ];

        if (count($activeItems) < 1) {
            return $errorArray;
        }

        $hasEmptyItemId = false;

        foreach ($activeItems as $items) {
            // Check if the item_id field is empty
            if (isset($items['item_id']) && empty($items['item_id'])) {
                // Set the flag to true as we found at least one empty item_id array
                $hasEmptyItemId = true;
            }
        }

        // If at least one empty item_id array was found, return a JSON response
        if ($hasEmptyItemId) {
            return $errorArray;
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

        return $availableItems;
    }

    /**
     *  04. Requested qty > available items on schedule (Fail)
     */
    public function checkRequestQtyAndAvailableQty(array $availableItems): array|null
    {
        foreach ($availableItems as $key => $availableItemsPerRequest) {
            $modelName = ItemGroup::where('id', $key)->first()->model_name;
            $numberOfAvailableItems = count($availableItemsPerRequest['item_id']->toArray());
            $requestedQty = $availableItemsPerRequest['quantity'];

            if ($requestedQty > $numberOfAvailableItems) {
                $isAre = $numberOfAvailableItems > 1 ? 'are' : 'is';
                return [
                    'status' => false,
                    'message' => 'Only ' . $numberOfAvailableItems . ' ' . $modelName . ' ' . $isAre . ' available for the selected dates',
                    'method' => "POST"
                ];
            }
        }
        return null;
    }

    /**
     *  05. Requested qty < available items on schedule (SHUFFLE)
     *      DO NOTHING for Requested qty === available items on sched
     */
    public function shuffleAvailableItems(array $availableItems): array
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


    // FOR REQUESTS WITH MULTIPLE OFFICES
    // 06. Group chosen items by office
    public function groupFinalItemListByOffice(array $finalItemList): array
    {
        $groupedItemList = [];
        foreach ($finalItemList as $itemGroupId => $chosenItems) {
            $office = ItemGroup::getOfficeById(itemGroupId: $itemGroupId);

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

}
