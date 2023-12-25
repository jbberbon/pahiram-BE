<?php

namespace App\Services;

use App\Models\BorrowedItem;
use Carbon\Carbon;

class ItemAvailability
{
    public static function isAvailable($itemId, $startDate, $returnDate)
    {
        $dateFormat = "Y-m-d H:i:s";
        $requestStartDate = Carbon::createFromFormat($dateFormat, $startDate);
        $requestReturnDate = Carbon::createFromFormat($dateFormat, $returnDate);

        $borrowedItems = BorrowedItem::where('item_id', $itemId)->get();

        if ($borrowedItems->isEmpty()) {
            // No existing borrowed items for the specified item ID, so it's available
            return true;
        }

        foreach ($borrowedItems as $borrowedItem) {
            $itemStartDate = Carbon::createFromFormat($dateFormat, $borrowedItem->start_date);
            $itemDueDate = Carbon::createFromFormat($dateFormat, $borrowedItem->due_date);

            // Check for overlap
            if ($itemStartDate < $requestReturnDate && $requestStartDate < $itemDueDate) {            
                // Overlap detected, item is not available
                return false;
            }
        }

        // No overlap detected, item is available
        return true;
    }
}