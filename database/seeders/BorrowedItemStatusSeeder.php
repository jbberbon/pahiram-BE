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
            [
                "borrowed_item_status_code" => 1010,
                "borrowed_item_status" => "reserved",
                "description" => "Item is awaiting approval"
            ],
            [
                "borrowed_item_status_code" => 2020,
                "borrowed_item_status" => "borrowed",
                "description" => "Item currently borrowed"
            ],
            [
                "borrowed_item_status_code" => 3030,
                "borrowed_item_status" => "declined",
                "description" => "Item is declined to be borrowed"
            ],
            [
                "borrowed_item_status_code" => 4040,
                "borrowed_item_status" => "overdue return",
                "description" => "Item is overdue for return"
            ],
            [
                "borrowed_item_status_code" => 5050,
                "borrowed_item_status" => "returned",
                "description" => "Item has been returned after a borrowing transaction"
            ],
            [
                "borrowed_item_status_code" => 6060,
                "borrowed_item_status" => "for repair",
                "description" => "Returned item requires repair / maintenance"
            ],
            [
                "borrowed_item_status_code" => 7070,
                "borrowed_item_status" => "unrepairable",
                "description" => "Returned item beyond fixing"
            ],
            [
                "borrowed_item_status_code" => 8080,
                "borrowed_item_status" => "lost",
                "description" => "Item is lost by the borrower"
            ]
        ];
        foreach ($borrowed_item_status as $status) {
            BorrowedItemStatus::create($status);
        }
    }
}
