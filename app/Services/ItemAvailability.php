<?php

namespace App\Services;

use App\Models\BorrowedItem;
use App\Services\RetrieveStatusService\BorrowedItemStatusService;
use App\Utils\DateUtil;
use Carbon\Carbon;
use Illuminate\Support\Collection;

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


    /**
     * Combines overlapping and adjacent date ranges, splitting them where necessary.
     *
     * @param array $borrowedItems Array of borrowed items with start/end times and counts.
     * @param int $maxAvailableItems The maximum number of available items.
     * @return array The processed array with split and combined date ranges.
     */
    public function processBorrowedDates(array $borrowedItems, int $maxAvailableItems): array
    {
        // Sort items by start date
        usort($borrowedItems, function ($a, $b) {
            return strtotime($a['start']) - strtotime($b['start']);
        });

        $result = [];
        $currentSlot = null;

        foreach ($borrowedItems as $item) {
            $start = $item['start'];
            $end = $item['end'];
            $count = $item['count'];

            if ($currentSlot === null) {
                // Start the first time slot
                $currentSlot = $item;
            } else {
                // Check if the current item overlaps with the previous one
                if (strtotime($currentSlot['end']) > strtotime($start)) {
                    // There's an overlap: merge the time slot and update the count
                    $currentSlot['end'] = max($currentSlot['end'], $end);
                    $currentSlot['count'] += $count;

                    // Apply "Fully Booked" title if the count reaches or exceeds max available items
                    if ($currentSlot['count'] >= $maxAvailableItems) {
                        $currentSlot['title'] = "Fully Booked";
                        $currentSlot['color'] = "#f44336";
                        $currentSlot['count'] = $maxAvailableItems;
                    }
                } else {
                    // No overlap: add the previous time slot to the result
                    $result[] = $currentSlot;
                    // Start a new slot
                    $currentSlot = $item;
                }
            }
        }

        // Add the last slot to the result
        if ($currentSlot !== null) {
            $result[] = $currentSlot;
        }

        // Adjust final result to ensure no duplicate or empty time slots
        return self::fixTimeSlots($result);
    }

    // Helper function to split and fix overlapping time slots
    function fixTimeSlots(array $timeSlots): array
    {
        $fixedSlots = [];

        foreach ($timeSlots as $slot) {
            // If the start time is equal to the end time, skip the slot
            if (strtotime($slot['start']) >= strtotime($slot['end'])) {
                continue;
            }

            $fixedSlots[] = $slot;
        }

        return $fixedSlots;
    }



}