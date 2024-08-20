<?php

namespace App\Utils\Constants\Statuses;

class BORROWED_ITEM_STATUS
{
    const PENDING_APPROVAL = 'PENDING_APPROVAL';
    const APPROVED = 'APPROVED';
    const CANCELLED = 'CANCELLED';
    const DISAPPROVED = 'DISAPPROVED';
    const IN_POSSESSION = 'IN_POSSESSION';
    const UNRELEASED = 'UNRELEASED';
    const OVERDUE_RETURN = 'OVERDUE_RETURN';
    const RETURNED = 'RETURNED';
    const UNRETURNED = 'UNRETURNED';
    const DAMAGED_BUT_REPAIRABLE = 'DAMAGED_BUT_REPAIRABLE';
    const UNREPAIRABLE = 'UNREPAIRABLE';
    const LOST = 'LOST';

    const BORROWED_ITEM_STATUS_ARRAY = [
        PENDING_APPROVAL => [
            "status" => self::PENDING_APPROVAL,
            "description" => "Item is awaiting approval"
        ],
        APPROVED => [
            "status" => self::APPROVED,
            "description" => "Item is approved"
        ],
        CANCELLED => [
            "status" => self::CANCELLED,
            "description" => "Item is cancelled by borrower"
        ],
        DISAPPROVED => [
            "status" => self::DISAPPROVED,
            "description" => "Item is declined to be borrowed"
        ],
        IN_POSSESSION => [
            "status" => self::IN_POSSESSION,
            "description" => "Item currently borrowed"
        ],
        UNRELEASED => [
            "status" => self::UNRELEASED,
            "description" => "Item is not released to borrower"
        ],
        OVERDUE_RETURN => [
            "status" => self::OVERDUE_RETURN,
            "description" => "Item is overdue for return"
        ],
        RETURNED => [
            "status" => self::RETURNED,
            "description" => "Item has been returned after a borrowing transaction"
        ],
        UNRETURNED => [
            "status" => self::UNRETURNED,
            "description" => "Item is unreturned"
        ],
        DAMAGED_BUT_REPAIRABLE => [
            "status" => self::DAMAGED_BUT_REPAIRABLE,
            "description" => "Returned item requires repair / maintenance"
        ],
        UNREPAIRABLE => [
            "status" => self::UNREPAIRABLE,
            "description" => "Returned item beyond fixing"
        ],
        LOST => [
            "status" => self::LOST,
            "description" => "Item is lost by the borrower"
        ]
    ];
}