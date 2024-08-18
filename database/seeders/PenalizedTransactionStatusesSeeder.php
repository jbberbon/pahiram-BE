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
                'status' => 'UNPAID',
                'description' => 'Delinquent no longer able to pay penalty'
            ],
            [
                'status' => 'PAID',
                'description' => 'The penalty is paid through the cashier'
            ],
            [
                'status' => 'SETTLED',
                'description' => 'The penalty is settled by the Finance Supervisor through promissory note etc.'
            ],
            [
                'status' => 'PENDING_PAYMENT',
                'description' => 'The penalty is up for payment'
            ],
        ];
        foreach ($statuses as $status) {
            PenalizedTransactionStatuses::create($status);
        }
    }
}
