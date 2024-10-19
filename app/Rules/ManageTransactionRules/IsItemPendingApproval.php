<?php

namespace App\Rules\ManageTransactionRules;

use App\Models\BorrowedItem;
use App\Models\BorrowedItemStatus;
use App\Utils\Constants\Statuses\BORROWED_ITEM_STATUS;
use Illuminate\Contracts\Validation\Rule;

class IsItemPendingApproval implements Rule
{

    private $pendingItemApprovalStatusId;

    public function __construct()
    {
        $this->pendingItemApprovalStatusId = BorrowedItemStatus::getIdByStatus(BORROWED_ITEM_STATUS::PENDING_APPROVAL);
    }
    public function passes($attribute, $value)
    {
        return BorrowedItem::where('id', $value)
            ->where('borrowed_item_status_id', $this->pendingItemApprovalStatusId)
            ->exists();
    }

    public function message()
    {
        return "Item is not pending borrow approval";
    }
}