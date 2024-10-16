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

    public static function getCancelledTransactionId()
    {
        return BorrowTransactionStatus::getIdByStatus(TRANSAC_STATUS::CANCELLED);
    }

    public static function getDisapprovedTransactionId()
    {
        return BorrowTransactionStatus::getIdByStatus(TRANSAC_STATUS::DISAPPROVED);
    }

    public static function getOnGoingTransactionId()
    {
        return BorrowTransactionStatus::getIdByStatus(TRANSAC_STATUS::ON_GOING);
    }

    public static function getActiveTransactionIds()
    {
        return [
            self::getApprovedTransactionId(),
            self::getPendingEndorserApprovalTransactionId(),
            self::getPendingBorrowingApprovalTransactionId(),
            self::getOnGoingTransactionId()
        ];
    }

}
