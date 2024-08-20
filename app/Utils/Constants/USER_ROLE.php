<?php

namespace App\Utils\Constants;

class USER_ROLE
{
    const BORROWER = 'BORROWER';
    const SUPERVISOR = 'SUPERVISOR';
    const COSUPERVISOR = 'COSUPERVISOR';
    const PENALTY_MANAGER = 'PENALTY_MANAGER';
    const BORROWING_MANAGER = 'BORROWING_MANAGER';
    const INVENTORY_MANAGER = 'INVENTORY_MANAGER';

    const USER_ROLE_ARRAY = [
        "BORROWER" => [
            'role' => self::BORROWER,
            'description' => 'System borrower'
        ],
        "BORROWING_MANAGER" => [
            'role' => self::BORROWING_MANAGER,
            'description' => 'Manages borrowing transactions'
        ],
        "INVENTORY_MANAGER" => [
            'role' => self::INVENTORY_MANAGER,
            'description' => 'Manages inventory'
        ],
        "PENALTY_MANAGER" => [
            'role' => self::PENALTY_MANAGER,
            'description' => 'Manages borrowing panalties'
        ],
        "COSUPERVISOR" => [
            'role' => self::COSUPERVISOR,
            'description' => 'Alter ego of Supervisor but cannot designate office roles'
        ],
        "SUPERVISOR" => [
            'role' => self::SUPERVISOR,
            'description' => 'Head of the designated office'
        ],
        // January 30, 2024
        // NO need, Endorsers for now are identified using their @apc.edu.ph email
        // [
        //     'role' => 'ENDORSER',
        //     'description' => 'Borrowing Endorser'
        // ],
    ];
}