<?php

namespace App\Rules;

use App\Models\Item;
use App\Models\ItemStatus;
use Illuminate\Contracts\Validation\Rule;

class HasEnoughActiveItems implements Rule
{
    public function passes($attribute, $value): bool
    {
        $itemGroupID = $value['item_group_id'];
        $quantityRequested = $value['quantity'];
        
        $activeStatus = ItemStatus::where('item_status_code', 1010)->first();

        $itemCount = Item::where('item_group_id', $itemGroupID)
            ->where('item_status_id', $activeStatus->id)
            ->count();

        // Check if the requested quantity exceeds the available quantity
        return $itemCount >= $quantityRequested;
    }

    public function message(): string
    {
        return 'Borrow request quantity exceeds unit quantity in circulation';
    }
}