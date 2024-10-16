<?php

namespace App\Services\BorrowRequestService;

use App\Models\BorrowedItem;
use App\Models\BorrowPurpose;
use App\Models\User;
use App\Services\RetrieveStatusService\BorrowedItemStatusService;

class EditBorrowRequestService
{
    private $pendingBorrowedItemStatusId;
    private $cancelledBorrowedItemStatusId;

    public function __construct()
    {
        // Borrowed Item Statuses
        $this->pendingBorrowedItemStatusId = BorrowedItemStatusService::getPendingStatusId();
        $this->cancelledBorrowedItemStatusId = BorrowedItemStatusService::getCancelledStatusId();
    }
    public function prepareRequestUpdateArgs($validatedData)
    {
        // Retrieve "transaction" (item data not included) related details and patch it to tb
        $borrowRequestArgs = $validatedData;

        if (isset($borrowRequestArgs['endorsed_by'])) {
            $borrowRequestArgs['endorsed_by'] = User::getUserIdBasedOnApcId($validatedData['endorsed_by']);
        }

        if (isset($borrowRequestArgs['purpose'])) {
            // Change Purpose to Purpose_id 
            $borrowRequestArgs['purpose_id'] = BorrowPurpose::getIdByPurpose($borrowRequestArgs['purpose']);
            // Delete the original purpose request field as it is no longer needed
            unset($borrowRequestArgs['purpose']);
        }
        if (isset($borrowRequestArgs['apcis_token'])) {
            // Remove APCIS Token as it is not part of the Transaction Table
            unset($borrowRequestArgs['apcis_token']);
        }

        if (isset($borrowRequestArgs['department'])) {
            unset($borrowRequestArgs['department']);
        }

        if (isset($borrowRequestArgs['items'])) {
            unset($borrowRequestArgs['items']);
        }

        return $borrowRequestArgs;
    }

    /**
     * Segregates items to be edited into three categories:
     *
     * @param array $toBeEdited Array of items to be segregated for editing.
     * @return array Segregated array includes keys:
     *  - toBeEdited: Items where dates and/or quantities need to be changed.
     *  - toBeCancelledIds: Item group IDs that are marked as cancelled.
     *  - toBeQtyChangedOnly: Items where only the quantity needs to be changed, without any date changes.
     */
    public function segregateToBeEditedItems(array $toBeEdited): array
    {
        $toBeCancelledIds = [];
        $toBeQtyChangedOnly = [];

        // Single loop to handle both cancelled items and quantity-only changes
        foreach ($toBeEdited as $index => $item) {
            if (isset($item['is_cancelled']) && $item['is_cancelled'] === true) {
                // Add cancelled item's group ID to $toBeCancelledIds array
                $toBeCancelledIds[] = $item['item_group_id'];
                // Remove the cancelled item from $toBeEdited
                unset($toBeEdited[$index]);
            } elseif (
                // If quantity only to be changed
                !isset($item['start_date']) &&
                !isset($item['return_date']) &&
                isset($item['quantity'])
            ) {
                // Filter the items that are ONLY to be changed in Qty
                $toBeQtyChangedOnly[] = $item;
                // Remove the toBeQtyChangedOnly item from $toBeEdited
                unset($toBeEdited[$index]);
            }
        }

        return [
            'toBeEdited' => $toBeEdited,
            'toBeCancelledIds' => $toBeCancelledIds,
            'toBeQtyChangedOnly' => $toBeQtyChangedOnly
        ];
    }

    public function processToBeQtyChangedOnly(string $transacId, array $toBeQtyChangedOnly): array
    {
        $toBeCancelledBorrowedItemIds = [];
        $itemsToBeAdded = [];
        foreach ($toBeQtyChangedOnly as $item) {
            // Get the active qty of that specific model
            $activeQty = BorrowedItem::getActiveModelItemQtyInTransaction(
                transacId: $transacId,
                itemGroupId: $item['item_group_id']
            );

            $absoluteDifference = abs($activeQty - $item['quantity']);

            // User wants to SUBTRACT items
            if ($activeQty > $item['quantity']) {
                $toBeSubtracted = BorrowedItem::where('borrowing_transac_id', $transacId)
                    ->join('items', 'borrowed_items.item_id', '=', 'items.id')
                    ->join('item_groups', 'items.item_group_id', '=', 'item_groups.id')
                    ->where('item_groups.id', $item['item_group_id']) // Filter by item group ID
                    ->limit($absoluteDifference)
                    ->pluck('borrowed_items.id')
                    ->toArray();

                // Add it to the toBeCancelled Array
                $toBeCancelledBorrowedItemIds = array_unique(
                    array_merge($toBeCancelledBorrowedItemIds, $toBeSubtracted)
                );
            }


            // User wants to ADD more qty of an existing item in request
            if ($activeQty < $item['quantity']) {
                // Get the start and due date ONLY including keys
                $itemStartAndReturnDate = BorrowedItem::where('borrowing_transac_id', $transacId)
                    ->join('items', 'borrowed_items.item_id', '=', 'items.id')
                    ->join('item_groups', 'items.item_group_id', '=', 'item_groups.id')
                    ->where('item_groups.id', $item['item_group_id']) // Filter by item group ID
                    ->select('borrowed_items.start_date', 'borrowed_items.due_date as return_date')  // Select specific columns
                    ->first() // Retrieve the first matching record
                    ->toArray();

                // Group it in a single array to be processed later
                $itemsToBeAdded[] = [
                    ...$item,
                    ...$itemStartAndReturnDate,
                    'quantity' => $absoluteDifference
                ];
            }

        }

        return [
            'toBeCancelledBorrowedItemIds' => $toBeCancelledBorrowedItemIds,
            'itemsToBeAdded' => $itemsToBeAdded
        ];
    }
}