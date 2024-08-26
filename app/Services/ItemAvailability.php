<?php

namespace App\Services;

use App\Models\BorrowedItem;
use App\Services\RetrieveStatusService\BorrowedItemStatusService;
use Carbon\Carbon;

class ItemAvailability
{
    private $pendingStatusBorrowedItemId;
    private $inPossessionStatusBorrowedItemId;
    private $approvedStatusBorrowedItemId;

    private $overdueStatusBorrowedItemId;


    public function __construct()
    {
        $this->pendingStatusBorrowedItemId = BorrowedItemStatusService::getPendingStatusId();
        $this->inPossessionStatusBorrowedItemId = BorrowedItemStatusService::getInPossessionStatusId();
        $this->approvedStatusBorrowedItemId = BorrowedItemStatusService::getApprovedStatusId();

        $this->overdueStatusBorrowedItemId = BorrowedItemStatusService::getOverdueStatusId();
    }



    public function isAvailable($itemId, $startDate, $returnDate)
    {
        $dateFormat = "Y-m-d H:i:s";
        $requestStartDate = Carbon::createFromFormat($dateFormat, $startDate);
        $requestReturnDate = Carbon::createFromFormat($dateFormat, $returnDate);

        // List all the IDs of the borrowedItemStatuses that is currently occupied
        $occupiedItemsStatusIds = [
            $this->pendingStatusBorrowedItemId,
            $this->inPossessionStatusBorrowedItemId,
            $this->approvedStatusBorrowedItemId,

            // I think this should have a separate logic
            $this->overdueStatusBorrowedItemId,
        ];

        // Check first if BorrowedItems Table has an item (with the ItemId) 
        // that is currently borrowed
        // !!Remember!! >> An individual ItemId can be stored many times in BorrowedItem table
        $borrowedItems = BorrowedItem::where('item_id', $itemId)
            ->whereIn('borrowed_item_status_id', $occupiedItemsStatusIds)
            ->get();

        // If no existing borrowedItems for the specified itemID, 
        // then it's available
        if ($borrowedItems->isEmpty()) {
            return true;
        }

        // if it returned borrowedItems with the relevant statuses, 
        // Lets check for date overlaps
        foreach ($borrowedItems as $borrowedItem) {
            if ($borrowedItem->start_date && $borrowedItem->due_date) { // Handle null date values in BorrowedItem TB
                $itemStartDate = Carbon::createFromFormat($dateFormat, $borrowedItem->start_date);
                $itemDueDate = Carbon::createFromFormat($dateFormat, $borrowedItem->due_date);

                // Check for overlap
                if ($itemStartDate < $requestReturnDate && $requestStartDate < $itemDueDate) {
                    // Overlap detected, item is not available
                    return false;
                }
            }
            // Check if the item is already overdue
            // if ($borrowedItem->borrowed_item_status_id == $overdueReturnStatus->id) {
            //     return false;
            // }
        }

        // No overlap detected, item is available
        return true;
    }
}