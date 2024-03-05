<?php

namespace App\Utils\Constants\Statuses;

class PENALIZED_TRANSAC_STATUS
{
    const PENDING_PAYMENT = 'PENDING_PAYMENT';
    const SETTLED = 'SETTLED';
    const UNPAID = 'UNPAID';
    const PAID = 'PAID';

    const ALL = [
        self::PENDING_PAYMENT,
        self::SETTLED,
        self::UNPAID,
        self::PAID
    ];
}