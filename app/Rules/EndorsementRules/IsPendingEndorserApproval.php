<?php

namespace App\Rules\EndorsementRules;

use App\Models\BorrowTransaction;
use App\Services\RetrieveStatusService\BorrowTransactionStatusService;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class IsPendingEndorserApproval implements Rule
{
    public function passes($attribute, $value)
    {
        // Check if the transaction is Pending Endorser Approval
        $pendingEndorserApprovalId = BorrowTransactionStatusService::getPendingEndorserApprovalTransactionId();
        $transaction = BorrowTransaction::where('id', $value)->first();

        if (!$transaction) {
            return false;
        }

        return $transaction->transac_status_id === $pendingEndorserApprovalId;
    }

    public function message()
    {
        return 'Unauthorized access';
    }
}
