<?php

namespace App\Services\RetrieveStatusService;
use App\Models\ItemStatus;
use App\Utils\Constants\Statuses\ITEM_STATUS;


class ItemStatusService {
    public static function getActiveStatusId() {
        // return BorrowTransactionStatus::getIdByStatus(TRANSAC_STATUS::PENDING_APPROVAL);
        return ItemStatus::getIdByStatus(ITEM_STATUS::ACTIVE);
    }
}