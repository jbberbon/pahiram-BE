<?php

namespace App\Utils\Constants\Statuses;

class BORROWED_ITEM_STATUS
{
    const PENDING_APPROVAL = 'PENDING_APPROVAL';
    const APPROVED = 'APPROVED';
    const IN_POSSESSION = 'IN_POSSESSION';
    const CANCELLED = 'CANCELLED';
    const DISAPPROVED = 'DISAPPROVED';
    const UNRELEASED = 'UNRELEASED';
    const RETURNED = 'RETURNED';
    const UNRETURNED = 'UNRETURNED';
    const DAMAGED_BUT_REPAIRABLE = 'DAMAGED_BUT_REPAIRABLE';
    const UNREPAIRABLE = 'UNREPAIRABLE';
    const LOST = 'LOST';

    const BORROWED_ITEM_STATUS_ARRAY = [
        "PENDING_APPROVAL" => [
            "borrowed_item_status" => self::PENDING_APPROVAL,
            "description" => "Item is awaiting approval"
        ],
        "APPROVED" => [
            "borrowed_item_status" => self::APPROVED,
            "description" => "Item is approved"
        ],
        "CANCELLED" => [
            "borrowed_item_status" => self::CANCELLED,
            "description" => "Item is cancelled by borrower"
        ],
        "DISAPPROVED" => [
            "borrowed_item_status" => self::DISAPPROVED,
            "description" => "Item is declined to be borrowed"
        ],
        "IN_POSSESSION" => [
            "borrowed_item_status" => self::IN_POSSESSION,
            "description" => "Item currently borrowed"
        ],
        "UNRELEASED" => [
            "borrowed_item_status" => self::UNRELEASED,
            "description" => "Item is not released to borrower"
        ],
        "RETURNED" => [
            "borrowed_item_status" => self::RETURNED,
            "description" => "Item has been returned after a borrowing transaction"
        ],
        "UNRETURNED" => [
            "borrowed_item_status" => self::UNRETURNED,
            "description" => "Item is unreturned"
        ],
        "DAMAGED_BUT_REPAIRABLE" => [
            "borrowed_item_status" => self::DAMAGED_BUT_REPAIRABLE,
            "description" => "Returned item requires repair / maintenance"
        ],
        "UNREPAIRABLE" => [
            "borrowed_item_status" => self::UNREPAIRABLE,
            "description" => "Returned item beyond fixing"
        ],
        "LOST" => [
            "borrowed_item_status" => self::LOST,
            "description" => "Item is lost by the borrower"
        ]
    ];

    const RETURNED_STATUSES = [
        'RETURNED' => self::RETURNED,
        'DAMAGED_BUT_REPAIRABLE' => self::DAMAGED_BUT_REPAIRABLE,
        'UNREPAIRABLE' => self::UNREPAIRABLE
    ];

    const UNRETURNED_STATUSES = [
        'UNRETURNED' => self::UNRETURNED,
        'LOST' => self::LOST
    ];

    const PENALIZED_STATUSES = [
        'DAMAGED_BUT_REPAIRABLE' => self::DAMAGED_BUT_REPAIRABLE,
        'UNREPAIRABLE' => self::UNREPAIRABLE,
        'UNRETURNED' => self::UNRETURNED,
        'LOST' => self::LOST
    ];
}