<?php

namespace App\Rules;

use App\Services\RetrieveStatusService\BorrowTransactionStatusService;
use Illuminate\Contracts\Validation\Rule;
use App\Models\BorrowTransactionStatus;

class CancelTransacRule implements Rule
{
    private $pendingEndorserApprovalTransacStatusId;
    private $pendingBorrowingApprovalTransacStatusId;

    private $approvedTransacStatusId;

    public function __construct()
    {
        $this->pendingEndorserApprovalTransacStatusId = BorrowTransactionStatusService::getPendingEndorserApprovalTransactionId();
        $this->pendingBorrowingApprovalTransacStatusId = BorrowTransactionStatusService::getPendingBorrowingApprovalTransactionId();
        $this->approvedTransacStatusId = BorrowTransactionStatusService::getApprovedTransactionId();
    }

    public function passes($attribute, $value)
    {
        return \DB::table('borrow_transactions')
            ->where('id', $value)
            ->whereIn('transac_status_id', [
                $this->pendingEndorserApprovalTransacStatusId,
                $this->pendingBorrowingApprovalTransacStatusId,
                $this->approvedTransacStatusId,
            ])
            ->exists();
    }

    public function message()
    {
        return 'The selected :attribute is invalid.';
    }
}
