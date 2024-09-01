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

    public static function getApprovedTransactionId()
    {
        return BorrowTransactionStatus::getIdByStatus(TRANSAC_STATUS::APPROVED);
    }

    // public static function getOverdueTransactionId()
    // {
    //     return BorrowTransactionStatus::getIdByStatus(TRANSAC_STATUS::OVERDUE_TRANSACTION_COMPLETION);
    // }

    public static function getCancelledTransactionId()
    {
        return BorrowTransactionStatus::getIdByStatus(TRANSAC_STATUS::CANCELLED);
    }

    public static function getDisapprovedTransactionId()
    {
        return BorrowTransactionStatus::getIdByStatus(TRANSAC_STATUS::DISAPPROVED);
    }

    public static function geOnGoingTransactionId()
    {
        return BorrowTransactionStatus::getIdByStatus(TRANSAC_STATUS::ON_GOING);
    }

}
