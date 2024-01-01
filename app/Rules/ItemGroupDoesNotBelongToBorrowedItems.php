<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;  // Make sure to import the Rule interface
use App\Models\BorrowedItem;

class ItemGroupDoesNotBelongToBorrowedItems implements Rule
{
    public function passes($attribute, $value)
    {
        $requestId = request()->route('requestId');

        // Check if the item_group_id does not belong to any borrowed item in the specific borrow transaction
        $itemGroupNotAssociatedWithBorrowedItem = !BorrowedItem::where('borrowing_transac_id', $requestId)
            ->whereHas('item', function ($query) use ($value) {
                $query->where('item_group_id', $value);
            })
            ->exists();

        return $itemGroupNotAssociatedWithBorrowedItem;
    }

    public function message()
    {
        return 'Item group id is associated with at least one borrowed item';
    }
}