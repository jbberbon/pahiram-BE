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
                "item_status" => "ACTIVE",
                "description" => "Item is in circulation",
            ],

            [
                "item_status" => "INACTIVE",
                "description" => "Item is undergoing repair / maintenance but will be active later",
            ],
            [
                "item_status" => "DESIGNATED",
                "description" => "Not borrowed but item is currently deployed to an APC employee",
            ],
            [
                "item_status" => "FOR_REPAIR",
                "description" => "Item is damaged thus, for repair",
            ],
            [
                "item_status" => "BEYOND_REPAIR",
                "description" => "Item is unfixable",
            ],
            [
                "item_status" => "RETIRED",
                "description" => "Item retired due to age",
            ],
            [
                "item_status" => "LOST",
                "description" => "Item is lost",
            ],
            [
                "item_status" => "UNRETURNED",
                "description" => "Item is unreturned by borrower or designated personnel",
            ],
        ];

        foreach ($item_status as $status) {
            ItemStatus::create($status);
        }
        ;
    }
}
