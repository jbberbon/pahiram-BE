<?php

namespace App\Utils\Constants\Statuses;

class PENALIZED_TRANSAC_STATUS
{
    const PENDING_SUPERVISOR_FINALIZATION = 'PENDING_SUPERVISOR_FINALIZATION';
    const PENDING_PAYMENT = 'PENDING_PAYMENT';
    const UNPAID = 'UNPAID';
    const PAID = 'PAID';

    const STATUS_KEYS = [
        self::PENDING_PAYMENT,
        self::SETTLED,
        self::UNPAID,
        self::PAID
    ];

    const PENALIZED_TRANSAC_STATUS_ARRAY = [
        "PENDING_SUPERVISOR_FINALIZATION" => [
            'status' => self::PENDING_SUPERVISOR_FINALIZATION,
            'description' => 'The penalty will be assessed and finalized by supervisor'
        ],
        "PENDING_PAYMENT" => [
            'status' => self::PENDING_PAYMENT,
            'description' => 'The penalty is up for payment'
        ],
        "UNPAID" => [
            'status' => self::UNPAID,
            'description' => 'Delinquent no longer able to pay penalty'
        ],
        "PAID" => [
            'status' => self::PAID,
            'description' => 'The penalty is paid through the cashier'
        ],
    ];
}