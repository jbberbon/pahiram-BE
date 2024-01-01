<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\BorrowedItemStatus;
use Illuminate\Support\Facades\DB;

class ItemGroupShouldHavePendingStatus implements Rule
{
    protected $pendingApprovalId;

    public function __construct()
    {
        $this->pendingApprovalId = BorrowedItemStatus::where('borrowed_item_status_code', 1010)->first()->id;
    }

    public function passes($attribute, $value)
    {
        // Check if the item_group_id is associated with a pending transaction
        return DB::table('items')
            ->where('item_group_id', $value)
            ->join('borrowed_items', 'items.id', '=', 'borrowed_items.item_id')
            ->where('borrowed_items.borrowed_item_status_id', $this->pendingApprovalId)
            ->exists();
    }

    public function message()
    {
        return 'The selected item group must be associated with a pending transaction.';
    }
}
