<?php

namespace Database\Seeders;

use App\Models\PenalizedTransactionStatuses;
use App\Utils\Constants\Statuses\PENALIZED_TRANSAC_STATUS;
use Illuminate\Database\Seeder;

class PenalizedTransactionStatusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = PENALIZED_TRANSAC_STATUS::PENALIZED_TRANSAC_STATUS_ARRAY;
        foreach ($statuses as $status) {
            PenalizedTransactionStatuses::create($status);
        }
    }
}
