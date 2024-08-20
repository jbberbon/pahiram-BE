<?php

namespace App\Utils\Constants\Statuses;

class ACCOUNT_STATUS
{
    const ACTIVE = 'ACTIVE';
    const DEACTIVATED = 'DEACTIVATED';
    const SUSPENDED = 'SUSPENDED';

    const STATUS_ARRAY = [
        "ACTIVE" => [
            'acc_status' => self::ACTIVE,
            'description' => 'The account is active and in good standing'
        ],
        "DEACTIVATED" => [
            'acc_status' => self::DEACTIVATED,
            'description' => 'The person is either have not enrolled yet or no longer affiliated with the school'
        ],
        "SUSPENDED" => [
            'acc_status' => self::SUSPENDED,
            'description' => 'The account needs to address outstanding penalties / issues'
        ],
    ];
}