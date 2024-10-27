<?php

namespace App\Services\RetrieveStatusService;
use App\Models\ItemStatus;
use App\Utils\Constants\Statuses\ITEM_STATUS;


class ItemStatusService
{
    public static function getActiveStatusId()
    {
        return ItemStatus::getIdByStatus(ITEM_STATUS::ACTIVE);
    }

    public static function getForRepairStatusId()
    {
        return ItemStatus::getIdByStatus(ITEM_STATUS::FOR_REPAIR);
    }
    public static function getBeyondRepairStatusId()
    {
        return ItemStatus::getIdByStatus(ITEM_STATUS::BEYOND_REPAIR);
    }

    public static function getUnreturnedRepairStatusId()
    {
        return ItemStatus::getIdByStatus(ITEM_STATUS::UNRETURNED);
    }
}