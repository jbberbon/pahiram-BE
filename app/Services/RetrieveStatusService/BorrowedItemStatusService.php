<?php

namespace App\Services\RetrieveStatusService;

use App\Models\BorrowedItemStatus;
use App\Utils\Constants\Statuses\BORROWED_ITEM_STATUS;

class BorrowedItemStatusService
{
    public static function getPendingStatusId(): string|null
    {
        return BorrowedItemStatus::getIdByStatus(BORROWED_ITEM_STATUS::PENDING_APPROVAL);
    }

    public static function getInPossessionStatusId(): string|null
    {
        return BorrowedItemStatus::getIdByStatus(BORROWED_ITEM_STATUS::IN_POSSESSION);
    }

    public static function getCancelledStatusId(): string|null
    {
        return BorrowedItemStatus::getIdByStatus(BORROWED_ITEM_STATUS::CANCELLED);
    }

    public static function getDisapprovedStatusId(): string|null
    {
        return BorrowedItemStatus::getIdByStatus(BORROWED_ITEM_STATUS::DISAPPROVED);
    }
    public static function getReturnedStatusId(): string|null
    {
        return BorrowedItemStatus::getIdByStatus(BORROWED_ITEM_STATUS::RETURNED);
    }
    public static function getUnreleasedStatusId(): string|null
    {
        return BorrowedItemStatus::getIdByStatus(BORROWED_ITEM_STATUS::UNRELEASED);
    }
    public static function getUnreturnedStatusId(): string|null
    {
        return BorrowedItemStatus::getIdByStatus(BORROWED_ITEM_STATUS::UNRETURNED);
    }
    public static function getApprovedStatusId(): string|null
    {
        return BorrowedItemStatus::getIdByStatus(BORROWED_ITEM_STATUS::APPROVED);
    }

    public static function getActiveStatuses(): array
    {
        return [
            self::getPendingStatusId(),
            self::getApprovedStatusId(),
            self::getInPossessionStatusId(),
            self::getUnreturnedStatusId()
        ];
    }

    // TODO
    public static function getPenalizedStatuses() {
        
    }
}