<?php

namespace Database\Seeders;

use App\Models\BorrowedItemStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BorrowedItemStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $borrowed_item_status = [
            // [
            //     "borrowed_item_status" => "PENDING_ENDORSER_APPROVAL",
            //     "description" => "Item is awaiting approval"
            // ],
            [
                "borrowed_item_status" => "PENDING_APPROVAL",
                "description" => "Item is awaiting approval"
            ],
            [
                "borrowed_item_status" => "APPROVED",
                "description" => "Item is approved"
            ],
            [
                "borrowed_item_status" => "IN_POSSESSION",
                "description" => "Item currently borrowed"
            ],
            [
                "borrowed_item_status" => "CANCELLED",
                "description" => "Item is cancelled by borrower"
            ],
            [
                "borrowed_item_status" => "DISAPPROVED",
                "description" => "Item is declined to be borrowed"
            ],
            [
                "borrowed_item_status" => "OVERDUE_RETURN",
                "description" => "Item is overdue for return"
            ],
            [
                "borrowed_item_status" => "RETURNED",
                "description" => "Item has been returned after a borrowing transaction"
            ],
            [
                "borrowed_item_status" => "DAMAGED_BUT_REPAIRABLE",
                "description" => "Returned item requires repair / maintenance"
            ],
            [
                "borrowed_item_status" => "UNREPAIRABLE",
                "description" => "Returned item beyond fixing"
            ],
            [
                "borrowed_item_status" => "LOST",
                "description" => "Item is lost by the borrower"
            ]
        ];
        foreach ($borrowed_item_status as $status) {
            BorrowedItemStatus::create($status);
        }
    }
}
