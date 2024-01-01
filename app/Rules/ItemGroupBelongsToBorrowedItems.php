<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;  // Make sure to import the Rule interface
use App\Models\BorrowedItem;

class ItemGroupBelongsToBorrowedItems implements Rule
{
    public function passes($attribute, $value)
    {
        $requestId = request()->route('requestId');

        // Check if the item_group_id is associated with any borrowed item in the specific borrow transaction
        $itemGroupAssociatedWithBorrowedItem = BorrowedItem::where('borrowing_transac_id', $requestId)
            ->whereHas('item', function ($query) use ($value) {
                $query->where('item_group_id', $value);
            })
            ->exists();

        return $itemGroupAssociatedWithBorrowedItem;
    }

    public function message()
    {
        return 'Item group id is not associated with any borrowed item';
    }
}
