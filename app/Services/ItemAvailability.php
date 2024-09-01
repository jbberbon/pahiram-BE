<?php

namespace App\Services;

use App\Models\BorrowedItem;
use App\Services\RetrieveStatusService\BorrowedItemStatusService;
use App\Utils\DateUtil;
use Carbon\Carbon;
use DateFormatUtil;

class ItemAvailability
{
    private $pendingStatusBorrowedItemId;
    private $inPossessionStatusBorrowedItemId;
    private $approvedStatusBorrowedItemId;

    private $occupiedItemsStatusIds;
    private $dateFormat;


    public function __construct()
    {
        $this->pendingStatusBorrowedItemId = BorrowedItemStatusService::getPendingStatusId();
        $this->inPossessionStatusBorrowedItemId = BorrowedItemStatusService::getInPossessionStatusId();
        $this->approvedStatusBorrowedItemId = BorrowedItemStatusService::getApprovedStatusId();

        // List all the IDs of the borrowedItemStatuses that is currently occupied
        $this->occupiedItemsStatusIds = [
            $this->pendingStatusBorrowedItemId,
            $this->inPossessionStatusBorrowedItemId,
            $this->approvedStatusBorrowedItemId,
        ];

        $this->dateFormat = DateUtil::STANDARD_DATE_FORMAT;
    }



    public function isAvailable(string $itemId, string $startDate, string $returnDate): bool
    {
        try {
            $requestStartDate = Carbon::createFromFormat($this->dateFormat, $startDate);
            $requestReturnDate = Carbon::createFromFormat($this->dateFormat, $returnDate);

            // Check first if BorrowedItems Table has an item (with the ItemId) 
            // that is currently borrowed
            // !!Remember!! >> An individual ItemId can be stored many times in BorrowedItem table
            $borrowedItems = BorrowedItem::where('item_id', $itemId)
                ->whereIn('borrowed_item_status_id', $this->occupiedItemsStatusIds)
                ->get();

            // AVAILABLE if no existing borrowedItems for the specified itemID, 
            if ($borrowedItems->isEmpty()) {
                return true;
            }

            // Lets check for date overlaps
            foreach ($borrowedItems as $borrowedItem) {
                // Check for overdue return item
                $isOverdueReturn = self::isBorrowedItemOverdue($borrowedItem->toArray());
                if ($isOverdueReturn) {
                    return false;
                }

                // Proceed to normal checking of overlapping dates
                if ($borrowedItem->start_date && $borrowedItem->due_date) {
                    $hasOverlap = DateUtil::hasOverlapBetweenDateRanges(
                        $borrowedItem->start_date,
                        $borrowedItem->due_date,
                        $requestStartDate,
                        $requestReturnDate
                    );

                    if ($hasOverlap) {
                        // Overlap detected, item is not available
                        return false;
                    }
                } else {
                    // Either start_date or due_date is missing, item is not available
                    return false;
                }
            }

            // No overlap detected, item is available
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function isBorrowedItemOverdue(array $borrowedItem): bool
    {
        // Check the item is still in possession first
        if (
            $borrowedItem['borrowed_item_status_id'] === $this->inPossessionStatusBorrowedItemId
        ) {
            // check if due date has lapsed
            return DateUtil::isDateLapsed($borrowedItem['due_date']);
        }

        // if item is not is possession, then just return false
        return false;
    }
}