<?php

namespace App\Services;

use App\Models\BorrowedItem;
use App\Models\BorrowedItemStatus;
use App\Services\RetrieveStatusService\BorrowedItemStatusService;
use Carbon\Carbon;

class ItemAvailability
{
    private $pendingStatusBorrowedItemId;
    private $inPossessionStatusBorrowedItemId;
    private $overdueStatusBorrowedItemId;


    public function __construct()
    {
        $this->pendingStatusBorrowedItemId = BorrowedItemStatusService::getPendingStatusId();
        $this->inPossessionStatusBorrowedItemId = BorrowedItemStatusService::getInPossessionStatusId();
        $this->overdueStatusBorrowedItemId = BorrowedItemStatusService::getOverdueStatusId();
    }



    public function isAvailable($itemId, $startDate, $returnDate)
    {
        $dateFormat = "Y-m-d H:i:s";
        $requestStartDate = Carbon::createFromFormat($dateFormat, $startDate);
        $requestReturnDate = Carbon::createFromFormat($dateFormat, $returnDate);

        // $pendingApprovalStatus = BorrowedItemStatus::where('borrowed_item_status_code', 1010)->first();
        // $borrowedStatus = BorrowedItemStatus::where('borrowed_item_status_code', 2020)->first();
        // $overdueReturnStatus = BorrowedItemStatus::where('borrowed_item_status_code', 5050)->first();

        $relevantStatuses = [
            $this->pendingStatusBorrowedItemId,
            $this->inPossessionStatusBorrowedItemId,
            $this->overdueStatusBorrowedItemId,
        ];
        $borrowedItems = BorrowedItem::where('item_id', $itemId)
            ->whereIn('borrowed_item_status_id', $relevantStatuses)
            ->get();
        // $borrowedItems = BorrowedItem::where('item_id', $itemId)->get();

        if ($borrowedItems->isEmpty()) {
            // No existing borrowed items for the specified item ID, so it's available
            return true;
        }

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