<?php

namespace Database\Seeders;

use App\Models\AccountStatus;
use App\Utils\Constants\Statuses\ACCOUNT_STATUS;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccountStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = ACCOUNT_STATUS::STATUS_ARRAY;
        
        foreach ($statuses as $status) {
            AccountStatus::create($status);
        }
    }
}
