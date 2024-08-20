<?php

namespace App\Utils\Constants;

class USER_ROLE
{
    const BORROWER = 'BORROWER';
    const SUPERVISOR = 'SUPERVISOR';
    const COSUPERVISOR = 'COSUPERVISOR';
    const BORROWING_MANAGER = 'BORROWING_MANAGER';
    const INVENTORY_MANAGER = 'INVENTORY_MANAGER';

    const USER_ROLE_ARRAY = [
        [
            'role' => self::BORROWER,
            'description' => 'System borrower'
        ],
        [
            'role' => self::BORROWING_MANAGER,
            'description' => 'Manages borrowing transactions'
        ],
        [
            'role' => self::INVENTORY_MANAGER,
            'description' => 'Manages inventory'
        ],
        [
            'role' => self::PENALTY_MANAGER,
            'description' => 'Manages borrowing panalties'
        ],
        [
            'role' => self::COSUPERVISOR,
            'description' => 'Alter ego of Supervisor but cannot designate office roles'
        ],
        [
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