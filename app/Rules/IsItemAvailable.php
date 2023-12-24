<?php

namespace App\Rules;

use App\Models\BorrowedItem;
use Illuminate\Contracts\Validation\Rule;
use Carbon\Carbon;

class IsItemAvailable implements Rule
{
    public function passes($attribute, $values)
    {
        return collect($values)->every(function ($item) {
            $borrowedItems = BorrowedItem::where('item_id', $item[0])->get();

            return $borrowedItems->every(function ($borrowedItem) use ($item) {
                $startDate = Carbon::parse($item[1]);
                $returnDate = Carbon::parse($item[2]);

                $borrowStartDate = Carbon::parse($borrowedItem->start_date);
                $borrowReturnDate = Carbon::parse($borrowedItem->return_date);

                // Check if the date range of the new item overlaps with any borrowed item
                return !($returnDate < $borrowStartDate || $startDate > $borrowReturnDate);
            });

        });
    }

    public function message()
    {
        return 'The selected item is not available for the specified date range.';
    }
}
