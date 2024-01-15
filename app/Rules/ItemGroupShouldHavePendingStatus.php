<?php

namespace App\Rules;

use App\Services\RetrieveStatusService\BorrowedItemStatusService;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ItemGroupShouldHavePendingStatus implements Rule
{
    protected $pendingApprovalId;

    public function __construct()
    {
        $this->pendingApprovalId = BorrowedItemStatusService::getPendingStatusId();
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
