<?php

namespace App\Rules\ManageTransactionRules;

use App\Models\BorrowTransaction;
use App\Models\BorrowTransactionStatus;
use App\Utils\Constants\Statuses\TRANSAC_STATUS;
use Illuminate\Contracts\Validation\Rule;

class IsTransactionPendingBorrowApprovalStatus implements Rule
{
    private $pendingBorrowApprovalStatusId;

    public function __construct()
    {
        $this->pendingBorrowApprovalStatusId = BorrowTransactionStatus::getIdByStatus(TRANSAC_STATUS::PENDING_BORROWING_APPROVAL);
    }
    public function passes($attribute, $value)
    {
        $transaction = BorrowTransaction::find($value);

        if (!$transaction) {
            return false;
        }

        return $transaction->transac_status_id === $this->pendingBorrowApprovalStatusId;
    }

    public function message()
    {
        return 'Transaction must be pending borrow approval status';
    }
}