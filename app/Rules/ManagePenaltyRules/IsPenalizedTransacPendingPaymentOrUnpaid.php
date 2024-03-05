<?php

namespace App\Rules\ManagePenaltyRules;

use App\Models\PenalizedTransaction;
use App\Models\PenalizedTransactionStatuses;
use App\Utils\Constants\Statuses\PENALIZED_TRANSAC_STATUS;
use Illuminate\Contracts\Validation\Rule;

class IsPenalizedTransacPendingPaymentOrUnpaid implements Rule
{
    private $pendingPaymentId;
    private $unpaidId;

    public function __construct()
    {
        $this->pendingPaymentId = PenalizedTransactionStatuses::getIdByStatus(PENALIZED_TRANSAC_STATUS::PENDING_PAYMENT);
        $this->unpaidId = PenalizedTransactionStatuses::getIdByStatus(PENALIZED_TRANSAC_STATUS::UNPAID);
    }
    public function passes($attribute, $value)
    {
        $penalizedTransac = PenalizedTransaction::find($value);

        if (!in_array($penalizedTransac->status_id, [$this->pendingPaymentId, $this->unpaidId])) {
            return false;
        }
        return true;
    }

    public function message()
    {
        return "Transaction is already paid / settled";
    }
}