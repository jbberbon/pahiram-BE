<?php

namespace App\Rules\ManageTransactionRules;

use App\Models\BorrowTransaction;
use App\Models\BorrowTransactionStatus;
use App\Utils\Constants\Statuses\TRANSAC_STATUS;
use Illuminate\Contracts\Validation\Rule;

class IsTransactionOnGoingOrUnreturned implements Rule
{
    private $ongoingStatusId;
    private $unreturnedStatusId;

    public function __construct()
    {
        $this->ongoingStatusId = BorrowTransactionStatus::getIdByStatus(TRANSAC_STATUS::ON_GOING);
        $this->unreturnedStatusId = BorrowTransactionStatus::getIdByStatus(TRANSAC_STATUS::UNRETURNED);
    }
    public function passes($attribute, $value)
    {
        $transaction = BorrowTransaction::find($value);

        if (!$transaction) {
            return false;
        }

        return $transaction->transac_status_id === $this->ongoingStatusId ||
            $transaction->transac_status_id === $this->unreturnedStatusId;
    }

    public function message()
    {
        return 'Transaction must be ongoing or unreturned';
    }
}