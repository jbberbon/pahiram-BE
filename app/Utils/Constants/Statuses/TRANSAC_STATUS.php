<?php

namespace App\Utils\Constants\Statuses;

class TRANSAC_STATUS
{
    const PENDING_ENDORSER_APPROVAL = 'PENDING_ENDORSER_APPROVAL';
    const PENDING_BORROWING_APPROVAL = 'PENDING_BORROWING_APPROVAL';
    const APPROVED = 'APPROVED';
    const ON_GOING = 'ON_GOING';
    const DISAPPROVED = 'DISAPPROVED';
    const UNRELEASED = 'UNRELEASED';
    const CANCELLED = 'CANCELLED';
    const OVERDUE = 'OVERDUE_TRANSACTION_COMPLETION';
    const UNRETURNED = 'UNRETURNED';
    const TRANSACTION_COMPLETE = 'TRANSACTION_COMPLETE';

    const TRANSAC_STATUS_ARRAY = [
        PENDING_ENDORSER_APPROVAL => [
            "transac_status" => self::PENDING_ENDORSER_APPROVAL,
            "description" => "Transaction is awaiting for endorser approval",
        ],
        PENDING_BORROWING_APPROVAL => [
            "transac_status" => self::PENDING_BORROWING_APPROVAL,
            "description" => "Transaction is awaiting for borrowing approval",
        ],
        APPROVED => [
            "transac_status" => self::APPROVED,
            "description" => "All items within the transaction are approved",
        ],
        ON_GOING => [
            "transac_status" => self::ON_GOING,
            "description" => "All items are currently in the possesion of the borrower",
        ],
        DISAPPROVED => [
            "transac_status" => self::DISAPPROVED,
            "description" => "Transaction is disapproved",
        ],
        UNRELEASED => [
            "transac_status" => self::UNRELEASED,
            "description" => "All items are unreleased to the borrower",
        ],
        CANCELLED => [
            "transac_status" => self::CANCELLED,
            "description" => "Transaction is cancelled",
        ],
        OVERDUE_TRANSACTION_COMPLETION => [
            "transac_status" => self::OVERDUE_TRANSACTION_COMPLETION,
            "description" => "Transaction is complete and all item/s are returned",
        ],
        UNRETURNED => [
            "transac_status" => self::UNRETURNED,
            "description" => "All items are unreturned",
        ],
        TRANSACTION_COMPLETE => [
            "transac_status" => self::TRANSACTION_COMPLETE,
            "description" => "Transaction is complete and all item/s are returned",
        ],
    ];

}