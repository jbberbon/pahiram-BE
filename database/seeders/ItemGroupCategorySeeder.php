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
                "category_name" => "Cameras",
                "isConsumable" => false,
            ],
            [
                "category_name" => "Balls",
                "isConsumable" => false,
            ],
            [
                "category_name" => "lan cabling tools",
                "isConsumable" => false,
            ],
            [
                "category_name" => "paper",
                "isConsumable" => true,
            ],
            [
                "category_name" => "ballpen",
                "isConsumable" => true,
            ],
            [
                "category_name" => "pencil",
                "isConsumable" => true,
            ],
        ];

        foreach ($group_category as $category) {
            ItemGroupCategory::create($category);
        }
    }
}
