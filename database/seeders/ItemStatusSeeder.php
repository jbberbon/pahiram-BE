<?php

namespace Database\Seeders;

use App\Models\ItemStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItemStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $item_status = [
            [
                "item_status_code" => 1010,
                "item_status" => "Available",
                "description" => "Item is ready to be borrowed",
            ],
            [
                "item_status_code" => 2020,
                "item_status" => "Reserved",
                "description" => "Item is reserved to a borrower",
            ],
            [
                "item_status_code" => 3030,
                "item_status" => "Borrowed",
                "description" => "Item is currently borrowed",
            ],
            [
                "item_status_code" => 4040,
                "item_status" => "Possessed",
                "description" => "Item is currently deployed to a user",
            ],
            [
                "item_status_code" => 5050,
                "item_status" => "For Repair",
                "description" => "Item is ready / undergoing repair",
            ],
            [
                "item_status_code" => 6060,
                "item_status" => "Unrepairable",
                "description" => "Item is unfixable",
            ],
            [
                "item_status_code" => 7070,
                "item_status" => "Lost",
                "description" => "Item is lost",
            ],
            [
                "item_status_code" => 8080,
                "item_status" => "Retired",
                "description" => "Item retired due to age",
            ],
        ];

        foreach ($item_status as $status) {
            ItemStatus::create($status);
        }
        ;
    }
}
