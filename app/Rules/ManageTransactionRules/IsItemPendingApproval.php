<?php

namespace App\Rules\ManageTransactionRules;

use App\Models\BorrowedItem;
use App\Models\BorrowedItemStatus;
use App\Utils\Constants\Statuses\BORROWED_ITEM_STATUS;
use Illuminate\Contracts\Validation\Rule;

class IsItemPendingApproval implements Rule
{

    private $pendingItemApprovalStatusId;
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
        // $this->pendingBorrowApprovalStatusId = BorrowTransactionStatus::getIdByStatus(TRANSAC_STATUS::PENDING_BORROWING_APPROVAL);
        $this->pendingItemApprovalStatusId = BorrowedItemStatus::getIdByStatus(BORROWED_ITEM_STATUS::PENDING_APPROVAL);
    }
    public function passes($attribute, $value)
    {
        $borrowedItem = BorrowedItem::where('borrowing_transac_id', $this->request['transactionId'])
            ->where('borrowed_item_status_id', $this->pendingItemApprovalStatusId)
            ->join('items', 'items.id', '=', 'borrowed_items.item_id')
            ->join('item_groups', 'item_groups.id', '=', 'items.item_group_id')
            ->where('item_groups.id', $value)
            ->select('borrowed_items.borrowed_item_status_id as status')
            ->get();

        // the items are already filtered down.
        // Transactions may contain same item models, like 2 canon 200Ds
        // but only one instance of that item model will have 'PENDING APPROVAL' status
        // so the query basically filtered down the data to a single item
        return !$borrowedItem->isEmpty();
    }

    public function message()
    {
        return "Item is not pending borrow approval";
    }
}