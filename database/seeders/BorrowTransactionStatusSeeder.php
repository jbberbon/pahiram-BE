<?php

namespace Database\Seeders;

use App\Models\BorrowTransactionStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BorrowTransactionStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $transac_statuses = [
            [
                "transac_status" => "PENDING_ENDORSER_APPROVAL",
                "description" => "Transaction is awaiting for approval",
            ],
            [
                "transac_status" => "PENDING_BORROWING_APPROVAL",
                "description" => "Transaction is awaiting for approval",
            ],
            [
                "transac_status" => "APPROVED",
                "description" => "All items within the transaction are approved",
            ],
            [
                "transac_status" => "ON_GOING",
                "description" => "All items are currently in the possesion of the borrower",
            ],
            [
                "transac_status" => "DISAPPROVED",
                "description" => "Transaction is disapproved",
            ],
            [
                "transac_status" => "UNRELEASED",
                "description" => "All items are unreleased to the borrower",
            ],
            [
                "transac_status" => "CANCELLED",
                "description" => "Transaction is cancelled",
            ],
            [
                "transac_status" => "OVERDUE_TRANSACTION_COMPLETION",
                "description" => "Transaction is complete and all item/s are returned",
            ],
            [
                "transac_status" => "UNRETURNED",
                "description" => "All items are unreturned",
            ],
            [
                "transac_status" => "TRANSACTION_COMPLETE",
                "description" => "Transaction is complete and all item/s are returned",
            ],
        ];

        foreach ($transac_statuses as $status) {
            BorrowTransactionStatus::create($status);
        }
    }
}
