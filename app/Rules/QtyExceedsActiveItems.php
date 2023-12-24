<?php
namespace App\Rules;

use App\Models\ItemGroup;
use Illuminate\Contracts\Validation\Rule;

class QtyExceedsActiveItems implements Rule
{
    protected $itemGroupId;
    public function __construct($itemGroupId)
    {
        $this->itemGroupId = $itemGroupId;
    }
    public function passes($attribute, $value)
    {
        $itemGroup = ItemGroup::where('id', $this->itemGroupId)->first();

        if (!$itemGroup) {
            return false; // or handle the case where the item group is not found
        }

        if ($itemGroup->getGroupItems())

        return $itemGroup->getGroupItems();
    }

    public function message()
    {
        return 'Borrowing quantity exceeds active items.';
    }
}