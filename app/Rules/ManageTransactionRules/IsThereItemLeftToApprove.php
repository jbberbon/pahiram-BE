<?php

namespace App\Rules\ManageTransactionRules;

use App\Models\BorrowedItem;
use App\Models\BorrowedItemStatus;
use App\Models\BorrowTransaction;
use App\Models\BorrowTransactionStatus;
use App\Utils\Constants\Statuses\BORROWED_ITEM_STATUS;
use App\Utils\Constants\Statuses\TRANSAC_STATUS;
use Illuminate\Contracts\Validation\Rule;

class IsThereItemLeftToApprove implements Rule
{
    private $request;
    private $pendingApprovalStatusId;

    public function __construct($request)
    {
        $this->request = $request;
        $this->pendingApprovalStatusId = BorrowedItemStatus::getIdByStatus(BORROWED_ITEM_STATUS::PENDING_APPROVAL);
    }
    public function passes($attribute, $value)
    {
        $pendingItemCount = BorrowedItem::where('borrowing_transac_id', $this->request['transactionId'])
            ->where('borrowed_item_status_id', $this->pendingApprovalStatusId)
            ->count();

        if ($pendingItemCount === 0) {
            return false;
        }

        return true;
    }

    public function message()
    {
        return 'No more items left to approve';
    }
}