<?php

namespace Database\Seeders;

use App\Models\BorrowTransactionStatus;
use App\Utils\Constants\Statuses\TRANSAC_STATUS;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BorrowTransactionStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $transac_statuses = TRANSAC_STATUS::TRANSAC_STATUS_ARRAY;

        foreach ($transac_statuses as $status) {
            BorrowTransactionStatus::create($status);
        }
    }
}
