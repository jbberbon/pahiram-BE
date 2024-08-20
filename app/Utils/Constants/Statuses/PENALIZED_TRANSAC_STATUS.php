<?php

namespace App\Utils\Constants\Statuses;

class PENALIZED_TRANSAC_STATUS
{
    const PENDING_PAYMENT = 'PENDING_PAYMENT';
    const SETTLED = 'SETTLED';
    const UNPAID = 'UNPAID';
    const PAID = 'PAID';

    const STATUS_KEYS = [
        self::PENDING_PAYMENT,
        self::SETTLED,
        self::UNPAID,
        self::PAID
    ];

    const PENALIZED_TRANSAC_STATUS_ARRAY = [
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
        "SETTLED" => [
            'status' => self::SETTLED,
            'description' => 'The penalty is settled by the Finance Supervisor through promissory note etc.'
        ],
    ];
}