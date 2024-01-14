<?php

namespace Database\Seeders;

use App\Models\AccountStatus;
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
                'acc_status' => 'ACTIVE',
                'description' => 'The account is active and in good standing'
            ],
            [
                'acc_status' => 'DEACTIVATED',
                'description' => 'The person is either have not enrolled yet or no longer affiliated with the school'
            ],
            [
                'acc_status' => 'SUSPENDED',
                'description' => 'The account needs to address outstanding penalties / issues'
            ],
        ];
        foreach ($statuses as $status) {
            AccountStatus::create($status);
        }
    }
}
