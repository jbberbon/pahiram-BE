<?php

namespace App\Services\RetrieveStatusService;
use App\Models\ItemStatus;
use App\Models\PenalizedTransactionStatuses;
use App\Utils\Constants\Statuses\ITEM_STATUS;
use App\Utils\Constants\Statuses\PENALIZED_TRANSAC_STATUS;


class PenalizedTransactionStatusService
{
    public static function getPendingLendingSupervisorFinalization()
    {
        return PenalizedTransactionStatuses::getIdByStatus(PENALIZED_TRANSAC_STATUS::PENDING_LENDING_SUPERVISOR_FINALIZATION);
    }
    public static function getSettledAtLendingOfficeStatusId()
    {
        return PenalizedTransactionStatuses::getIdByStatus(PENALIZED_TRANSAC_STATUS::SETTLED_AT_LENDING_OFFICE);
    }
    public static function getPendingPaymentStatusId()
    {
        return PenalizedTransactionStatuses::getIdByStatus(PENALIZED_TRANSAC_STATUS::PENDING_PAYMENT);
    }
    public static function getUnpaidStatusId()
    {
        return PenalizedTransactionStatuses::getIdByStatus(PENALIZED_TRANSAC_STATUS::UNPAID);
    }
    public static function getPaidStatusId()
    {
        return PenalizedTransactionStatuses::getIdByStatus(PENALIZED_TRANSAC_STATUS::PAID);
    }
    public static function getSettledAtFinanceOffice()
    {
        return PenalizedTransactionStatuses::getIdByStatus(PENALIZED_TRANSAC_STATUS::SETTLED_AT_FINANCE_OFFICE);
    }

    public static function hashmap()
    {
        return PenalizedTransactionStatuses::pluck('status', 'id')->all();
    }

}