<?php

namespace App\Services\RetrieveStatusService;

use App\Models\BorrowTransactionStatus;
use App\Utils\Constants\Statuses\TRANSAC_STATUS;


class BorrowTransactionStatusService
{
    public static function getPendingEndorserApprovalTransactionId()
    {
        return BorrowTransactionStatus::getIdByStatus(TRANSAC_STATUS::PENDING_ENDORSER_APPROVAL);
    }
    public static function getPendingBorrowingApprovalTransactionId()
    {
        return BorrowTransactionStatus::getIdByStatus(TRANSAC_STATUS::PENDING_BORROWING_APPROVAL);
    }

    public static function getPendingApprovalTransactionId()
    {
        return BorrowTransactionStatus::getIdByStatus(TRANSAC_STATUS::PENDING_APPROVAL);
    }

    public static function getApprovedTransactionId()
    {
        return BorrowTransactionStatus::getIdByStatus(TRANSAC_STATUS::APPROVED);
    }

    public static function getOverdueTransactionId()
    {
        return BorrowTransactionStatus::getIdByStatus(TRANSAC_STATUS::OVERDUE);
    }

    public static function getCancelledTransactionId()
    {
        return BorrowTransactionStatus::getIdByStatus(TRANSAC_STATUS::CANCELLED);
    }


}
