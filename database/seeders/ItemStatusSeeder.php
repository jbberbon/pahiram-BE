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
                "item_status" => "Active",
                "description" => "Item is in circulation",
            ],
            [
                "item_status_code" => 2020,
                "item_status" => "Possessed",
                "description" => "Not borrowed but item is currently deployed to a user",
            ],
            [
                "item_status_code" => 3030,
                "item_status" => "For Repair",
                "description" => "Item is ready / undergoing repair",
            ],
            [
                "item_status_code" => 4040,
                "item_status" => "Unrepairable",
                "description" => "Item is unfixable",
            ],
            [
                "item_status_code" => 5050,
                "item_status" => "Lost",
                "description" => "Item is lost",
            ],
            [
                "item_status_code" => 6060,
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
