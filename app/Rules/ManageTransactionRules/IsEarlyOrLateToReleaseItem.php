<?php

namespace App\Rules\ManageTransactionRules;

use App\Models\BorrowedItem;
use App\Models\BorrowedItemStatus;
use App\Utils\Constants\Statuses\BORROWED_ITEM_STATUS;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class IsEarlyOrLateToReleaseItem implements Rule
{
    private $approvedStatusId;
    protected $request;
    protected $index;

    public function __construct($all)
    {
        $this->request = $all;
    }
    public function passes($attribute, $value)
    {
        // Extract index from the attribute path to access the specific item in request data
        if (preg_match('/items\.(\d+)\.is_released/', $attribute, $matches)) {
            $this->index = $matches[1];
        }

        // Get item data from the request based on the extracted index
        $itemData = $this->request['items'][$this->index] ?? null;

        // If the item is withheld (not released), skip date checks
        if ($itemData && $itemData['is_released'] === false) {
            return true;
        }

        // Find the borrowed item; if not found, validation fails
        $borrowedItem = BorrowedItem::find($value);
        if (!$borrowedItem) {
            return false;
        }

        // Convert start date to Carbon instance with timezone adjustment
        $startDate = Carbon::parse($borrowedItem->start_date)->setTimezone('Asia/Manila');
        $dueDate = Carbon::parse($borrowedItem->due_date)->setTimezone('Asia/Manila');

        // Get the current date and time in the same timezone
        $now = Carbon::now('Asia/Manila');

        // Return true only if the current time is after the item's start date
        // Also check if due date has already passed
        return $now->greaterThan($startDate) && $now->lessThan($dueDate);
    }

    public function message()
    {
        return "The item can only be released after its start date.";
    }
}