<?php

namespace App\Rules\ManageTransactionRules;

use App\Models\BorrowTransaction;
use App\Models\BorrowTransactionStatus;
use App\Utils\Constants\Statuses\TRANSAC_STATUS;
use Illuminate\Contracts\Validation\Rule;

class IsTransactionApprovedStatus implements Rule
{
    private $approvedStatusId;

    public function __construct()
    {
        $this->approvedStatusId = BorrowTransactionStatus::getIdByStatus(TRANSAC_STATUS::APPROVED);
    }
    public function passes($attribute, $value)
    {
        $transaction = BorrowTransaction::find($value);

        if (!$transaction) {
            return false;
        }

        return $transaction->transac_status_id === $this->approvedStatusId;
    }

    public function message()
    {
        return 'Transaction must be approved before releasing items';
    }
}