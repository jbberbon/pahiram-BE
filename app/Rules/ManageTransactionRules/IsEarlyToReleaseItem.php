<?php

namespace App\Rules\ManageTransactionRules;

use App\Models\BorrowedItem;
use App\Models\BorrowedItemStatus;
use App\Utils\Constants\Statuses\BORROWED_ITEM_STATUS;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class IsEarlyToReleaseItem implements Rule
{

    private $approvedStatusId;
    protected $request;

    public function passes($attribute, $value)
    {
        $borrowedItem = BorrowedItem::find($value);

        if (!$borrowedItem) {
            return false;
        }

        // Convert borrowed item start date to Carbon instance
        $start_date = Carbon::parse($borrowedItem->start_date);

        // Consider adjusting timezone if needed (replace 'Asia/Manila' with your actual timezone)
        $start_date->setTimezone('Asia/Manila');

        // Use Carbon to get current time in appropriate timezone
        $now = Carbon::now();
        $now->setTimezone('Asia/Manila');

        // Compare dates and times with Carbon methods
        return $now->greaterThan($start_date);

    }

    public function message()
    {
        return "Item is available for release after start date";
    }
}