<?php

namespace App\Utils\Constants\Statuses;

class PENALIZED_TRANSAC_STATUS
{
    const PENDING_LENDING_SUPERVISOR_FINALIZATION = 'PENDING_LENDING_SUPERVISOR_FINALIZATION';
    const PENDING_PAYMENT = 'PENDING_PAYMENT';
    const UNPAID = 'UNPAID';
    const PAID = 'PAID';
    const SETTLED_AT_LENDING_OFFICE = 'SETTLED_AT_LENDING_OFFICE';

    const STATUS_KEYS = [
        self::PENDING_PAYMENT,
        self::SETTLED,
        self::UNPAID,
        self::PAID
    ];

    const PENALIZED_TRANSAC_STATUS_ARRAY = [
        "PENDING_LENDING_SUPERVISOR_FINALIZATION" => [
            'status' => self::PENDING_LENDING_SUPERVISOR_FINALIZATION,
            'description' => 'The penalty will be assessed and finalized by lending supervisor'
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
        'SETTLED_AT_LENDING_OFFICE' => [
            'status' => self::SETTLED_AT_LENDING_OFFICE,
            'description' => 'The penalty is settled at the borrow office level facilitated by the supervisor'
        ]
    ];
}