<?php

namespace App\Utils\Constants\Statuses;

class ITEM_STATUS
{
    const ACTIVE = 'ACTIVE';
    const DESIGNATED = 'DESIGNATED';
    const FOR_REPAIR = 'FOR_REPAIR';
    const BEYOND_REPAIR = 'BEYOND_REPAIR';
    const DECOMMISSIONED = 'DECOMMISSIONED';
    const LOST = 'LOST';
    const UNRETURNED = 'UNRETURNED';


    const ITEM_STATUS_ARRAY = [
        "ACTIVE" => [
            "item_status" => self::ACTIVE,
            "description" => "Item is in circulation",
        ],
        "DESIGNATED" => [
            "item_status" => self::DESIGNATED,
            "description" => "Not borrowed but item is currently deployed to an APC employee",
        ],
        "FOR_REPAIR" => [
            "item_status" => self::FOR_REPAIR,
            "description" => "Item is damaged thus, for repair",
        ],
        "BEYOND_REPAIR" => [
            "item_status" => self::BEYOND_REPAIR,
            "description" => "Item is unfixable",
        ],
        "DECOMMISSIONED" => [
            "item_status" => self::DECOMMISSIONED,
            "description" => "Item retired due to age",
        ],
        "LOST" => [
            "item_status" => self::LOST,
            "description" => "Item is lost",
        ],
        "UNRETURNED" => [
            "item_status" => self::UNRETURNED,
            "description" => "Item is unreturned by borrower or designated personnel",
        ],
    ];
}