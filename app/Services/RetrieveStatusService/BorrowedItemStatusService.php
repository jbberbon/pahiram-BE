<?php

namespace App\Services\RetrieveStatusService;

use App\Models\BorrowedItemStatus;
use App\Utils\Constants\Statuses\BORROWED_ITEM_STATUS;

class BorrowedItemStatusService
{
    public static function getPendingStatusId()
    {
        return BorrowedItemStatus::getIdByStatus(BORROWED_ITEM_STATUS::PENDING_APPROVAL);
    }

    public static function getInPossessionStatusId()
    {
        return BorrowedItemStatus::getIdByStatus(BORROWED_ITEM_STATUS::IN_POSSESSION);
    }

    public static function getOverdueStatusId()
    {
        return BorrowedItemStatus::getIdByStatus(BORROWED_ITEM_STATUS::OVERDUE_RETURN);
    }

    public static function getCancelledStatusId()
    {
        return BorrowedItemStatus::getIdByStatus(BORROWED_ITEM_STATUS::CANCELLED);
    }

    public static function getDisapprovedStatusId()
    {
        return BorrowedItemStatus::getIdByStatus(BORROWED_ITEM_STATUS::DISAPPROVED);
    }

    public static function getApprovedStatusId()
    {
        return BorrowedItemStatus::getIdByStatus(BORROWED_ITEM_STATUS::APPROVED);
    }
}