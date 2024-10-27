<?php

namespace App\Rules\ManageTransactionRules;

use App\Models\BorrowTransaction;
use App\Models\BorrowTransactionStatus;
use App\Services\RetrieveStatusService\BorrowTransactionStatusService;
use App\Utils\Constants\Statuses\TRANSAC_STATUS;
use Illuminate\Contracts\Validation\Rule;

class IsTransactionApprovedOrOngoingStatus implements Rule
{
    private $approvedStatusId;
    private $ongoingStatusId;

    public function __construct()
    {
        $this->ongoingStatusId = BorrowTransactionStatusService::getOnGoingTransactionId();
        $this->approvedStatusId = BorrowTransactionStatusService::getApprovedTransactionId();
    }
    public function passes($attribute, $value)
    {
        $transaction = BorrowTransaction::find($value);

        if (!$transaction) {
            return false;
        }

        return
            $transaction->transac_status_id === $this->approvedStatusId ||
            $transaction->transac_status_id === $this->ongoingStatusId;
    }

    public function message()
    {
        return 'Transaction must be approved or ongoing before releasing items';
    }
}