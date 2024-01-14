<?php

namespace Database\Seeders;

use App\Models\ItemGroupCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItemGroupCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $group_category = [
            [
                "category_name" => "CAMERA",
                "is_consumable" => false,
            ],
            [
                "category_name" => "LAPTOP",
                "is_consumable" => false,
            ],
            [
                "category_name" => "BALL",
                "is_consumable" => false,
            ],
            [
                "category_name" => "LAN_CABLING_TOOL",
                "is_consumable" => false,
            ],
            [
                "category_name" => "PAPER",
                "is_consumable" => true,
            ],
            [
                "category_name" => "PENS",
                "is_consumable" => true,
            ],
            [
                "category_name" => "PENCIL",
                "is_consumable" => true,
            ],
        ];

        foreach ($group_category as $category) {
            ItemGroupCategory::create($category);
        }
    }
}
