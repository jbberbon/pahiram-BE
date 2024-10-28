<?php

namespace App\Rules\ManagePenalizedLendingTransactionRules;

use App\Models\BorrowTransaction;
use App\Services\RetrieveStatusService\PenalizedTransactionStatusService;
use Illuminate\Contracts\Validation\Rule;

class IsPenalizedTransactionPendingLendingSupervisorFinalization implements Rule
{
    private $pendingLendingSupFinalizationStatusId;

    public function __construct()
    {
        $this->pendingLendingSupFinalizationStatusId = PenalizedTransactionStatusService::getPendingLendingSupervisorFinalization();
    }
    public function passes($attribute, $value)
    {
        return BorrowTransaction::where('borrowing_transac_id', $value)
            ->where('status_id', $this->pendingLendingSupFinalizationStatusId)
            ->exists();
    }

    public function message()
    {
        return 'Transaction must be ongoing or unreturned';
    }
}