<?php

namespace App\Rules;

use App\Models\BorrowedItem;
use App\Models\Item;
use App\Models\ItemStatus;
use App\Utils\Constants\ItemStatusConst;
use Illuminate\Contracts\Validation\Rule;

class HasEnoughActiveItems implements Rule
{
    private $ACTIVE = ItemStatusConst::ACTIVE;
    public function passes($attribute, $value): bool
    {
        if (!isset($value['quantity'])) {
            // RULE FOR EDIT BORROW REQUEST but without quantity
            // IF w/ qty then same rule applies for EDIT AND SUBMIT REQUEST
            return true;
        }

        // RULE FOR SUBMIT BORROW REQUEST
        return $this->passesSubmitRule($value);
    }

    private function passesEditRule($value): bool
    {
        $borrowedItemId = $value['borrowed_item_id'];

        try {
            // Eager load relationships and handle ModelNotFoundException
            $borrowedItem = BorrowedItem::with(['item.itemGroup'])
                ->where('id', $borrowedItemId)
                ->firstOrFail();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle the case where BorrowedItem is not found
            return false;
        }

        $itemGroupId = optional($borrowedItem->item->itemGroup)->id;
        $quantityRequested = $value['quantity'];
        $activeStatus = ItemStatus::where('item_status_code', $this->ACTIVE)->first();

        return $this->checkQuantity($itemGroupId, $activeStatus->id, $quantityRequested);
    }

    private function passesSubmitRule($value): bool
    {
        $itemGroupId = $value['item_group_id'];
        $quantityRequested = $value['quantity'];
        $activeStatus = ItemStatus::where('item_status_code', 1010)->first();

        return $this->checkQuantity($itemGroupId, $activeStatus->id, $quantityRequested);
    }

    private function checkQuantity($itemGroupId, $itemStatusId, $quantityRequested): bool
    {
        $itemCount = Item::where('item_group_id', $itemGroupId)
            ->where('item_status_id', $itemStatusId)
            ->count();

        // Check if the requested quantity exceeds the available quantity
        return $itemCount >= $quantityRequested;
    }


    public function message(): string
    {
        return 'Borrow request quantity exceeds unit quantity in circulation';
    }
}