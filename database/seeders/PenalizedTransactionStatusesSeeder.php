<?php

namespace Database\Seeders;

use App\Models\PenalizedTransactionStatuses;
use Illuminate\Database\Seeder;

class PenalizedTransactionStatusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'status' => 'PENDING_SETTLEMENT',
                'description' => 'The penalty is up for settlement'
            ],
            [
                'status' => 'SETTLED',
                'description' => 'The penalty is settled'
            ],
            [
                'status' => 'UNSETTLED',
                'description' => 'Either Delinquent no longer wants to settle penalty or is unresponsive in payment followups'
            ],
        ];
        foreach ($statuses as $status) {
            PenalizedTransactionStatuses::create($status);
        }
    }
}
