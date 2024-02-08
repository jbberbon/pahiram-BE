<?php

namespace App\Rules\ManageTransactionRules;

use App\Models\BorrowedItem;
use App\Models\BorrowedItemStatus;
use App\Models\BorrowTransaction;
use App\Utils\Constants\Statuses\BORROWED_ITEM_STATUS;
use Illuminate\Contracts\Validation\Rule;

class IsItemGroupPartOfTransaction implements Rule
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
        $borrowedItemExists = BorrowedItem::where('borrowing_transac_id', $this->request['transactionId'])
            ->join('items', 'items.id', '=', 'borrowed_items.item_id')
            ->join('item_groups', 'item_groups.id', '=', 'items.item_group_id')
            ->where('item_groups.id', $value)
            ->exists();

        \Log::error('Borrowed Item ' . $borrowedItemExists);
        if ($borrowedItemExists) {
            return true;
        }
        return false;
    }

    public function message()
    {
        return 'Item does not belong to transaction';
    }
}