<?php

namespace App\Services;

use App\Models\BorrowedItem;
use Carbon\Carbon;

class CalculatePenalty
{
    public static function penaltyAmount($borrowedItemId)
    {
        if (!$borrowedItemId) {
            return 0;
        }

        $borrowedItem = BorrowedItem::find($borrowedItemId);

        if (!$borrowedItem) {
            return 0;
        }

        $dueDate = Carbon::parse($borrowedItem->due_date);

        // Calculate the difference in hours
        $timeElapsedHours = $dueDate->diffInHours(Carbon::now(), false);

        // Calculate penalty based on time elapsed
        if ($timeElapsedHours < 0) {
            // No penalty if returned before due date
            return 0;
        } elseif ($timeElapsedHours <= 24) {
            // Penalty for less than or equal to 24 hours late
            return 100;
        } else {
            // Penalty for more than 24 hours late
            $daysLate = floor($timeElapsedHours / 24);
            // Calculate penalty amount based on the number of days late
            return 100 + ($daysLate - 1) * 50;
        }
    }
}