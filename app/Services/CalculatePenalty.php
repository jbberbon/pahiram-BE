<?php

namespace App\Services;

use App\Models\BorrowedItem;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Collection;

class CalculatePenalty
{
    // public function calculateITROLatePenalty($dueDate): float
    // {
    //     //  TODO: TIP-> Create different time periods for penalty timer off
    //     $dueDate = Carbon::parse($dueDate);
    //     $now = Carbon::now();

    //     // If returned before due date
    //     if ($dueDate > $now) {
    //         return 0.0;
    //     }

    //     // Start the period from one hour after the due date
    //     $periodTimerOn = CarbonPeriod::create($dueDate->copy()->addHour(), '1 hour', $now);

    //     // PENALTY TIMER ON
    //     // 8 AM - 8 PM on weekdays (Monday to Friday)
    //     $weekdaysBusinessHours = $periodTimerOn->filter(function ($date) {
    //         return $date->isWeekday() && $date->hour >= 8 && $date->hour <= 20;
    //     })->count();

    //     // 8 AM - 4 PM (Saturday)
    //     $saturdayBusinessHours = $periodTimerOn->filter(function ($date) {
    //         return $date->isSaturday() && $date->hour >= 8 && $date->hour <= 16;
    //     })->count();

    //     $penaltyTimerOn = $weekdaysBusinessHours + $saturdayBusinessHours;

    //     // PENALTY TIMER OFF
    //     //
    //     $periodTimerOff = CarbonPeriod::create($dueDate->startOfDay(), '1 day', $now->endOfDay());

    //     // 8:01 PM to 7:59 AM (Mon - Sat)
    //     $weekdaysEveningToSatMorning = $periodTimerOn->filter(function ($date) {
    //         return $date->isWeekday() && $date->hour >= 20 && $date->hour < 8;
    //     })->count();
    // }

    public static function penaltyAmount(string $borrowedItemId): float|null
    {
        $borrowedItem = BorrowedItem::where('id', $borrowedItemId)->first();
        if (!$borrowedItem) {
            return null;
        }
        $dueDate = Carbon::parse($borrowedItem->due_date);
        $penaltyForFirstDay = 100;
        $penaltyForSucceedingDays = 50;

        // Calculate the difference in hours with decimal values
        $timeElapsedHours = $dueDate->diffInRealHours(Carbon::now(), false);

        // Calculate penalty based on time elapsed
        if ($timeElapsedHours < 0) {
            // No penalty if returned before due date
            return null;
        } elseif ($timeElapsedHours <= 24) {
            // Penalty for less than or equal to 24 hours late
            return $penaltyForFirstDay;
        } else {
            // Penalty for more than 24 hours late
            $daysLate = floor($timeElapsedHours / 24);
            // Calculate penalty amount based on the number of days late
            return $penaltyForFirstDay + (($daysLate - 1) * $penaltyForSucceedingDays);
        }
    }
}