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
                "transac_status_code" => 1010,
                "transac_status" => "Pending Approval",
                "description" => "Transaction is awaiting for approval",
            ],
            [
                "transac_status_code" => 2020,
                "transac_status" => "Approved",
                "description" => "All items within the transaction are approved",
            ],
            [
                "transac_status_code" => 3030,
                "transac_status" => "Partially Approved",
                "description" => "Not all items are approved",
            ],
            [
                "transac_status_code" => 4040,
                "transac_status" => "Released",
                "description" => "All items are currently in the possesion of the borrower",
            ],
            [
                "transac_status_code" => 5050,
                "transac_status" => "Declined",
                "description" => "Transaction is totally declined",
            ],
            [
                "transac_status_code" => 6060,
                "transac_status" => "Partially Declined",
                "description" => "Some transaction items are declined",
            ],
            [
                "transac_status_code" => 7070,
                "transac_status" => "Cancelled",
                "description" => "Transaction is cancelled",
            ],
            [
                "transac_status_code" => 8080,
                "transac_status" => "Complete",
                "description" => "Item/s are returned",
            ],
        ];

        foreach ($transac_statuses as $status) {
            BorrowTransactionStatus::create($status);
        }
    }
}
