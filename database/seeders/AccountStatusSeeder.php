<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccountStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'acc_status' => 'active',
                'acc_status_code' => 1010,
                'description' => 'The account is active and in good standing'
            ],
            [
                'acc_status' => 'suspended',
                'acc_status_code' => 2020,
                'description' => 'The account needs to address outstanding penalties / issues'
            ],
            [
                'acc_status' => 'retired',
                'acc_status_code' => 3030,
                'description' => 'The account is no longer active'
            ],
        ];
    }
}
