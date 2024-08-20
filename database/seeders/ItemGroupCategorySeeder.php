<?php

namespace Database\Seeders;

use App\Models\ItemGroupCategory;
use App\Utils\Constants\GROUP_CATEGORY_SAMPLE;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItemGroupCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groupCategorySample = new GROUP_CATEGORY_SAMPLE();

        $group_category = [
            // ITRO
            [
                "category_name" => $groupCategorySample->getGroupCategorySampleData("CAMERA"),
                "is_consumable" => false,
                "is_barcode_sticker_placeable" => true
            ],
            [
                "category_name" => $groupCategorySample->getGroupCategorySampleData("LAPTOP"),
                "is_consumable" => false,
                "is_barcode_sticker_placeable" => true
            ],
            [
                "category_name" => $groupCategorySample->getGroupCategorySampleData("MICROCONTROLLER"),
                "is_consumable" => false,
                "is_barcode_sticker_placeable" => true
            ],
            // BMO
            [
                "category_name" => $groupCategorySample->getGroupCategorySampleData("BALL"),
                "is_consumable" => false,
                "is_barcode_sticker_placeable" => false
            ],
            [
                "category_name" => $groupCategorySample->getGroupCategorySampleData("TABLE"),
                "is_consumable" => false,
                "is_barcode_sticker_placeable" => true
            ],
            [
                "category_name" => $groupCategorySample->getGroupCategorySampleData("SPEAKER"),
                "is_consumable" => false,
                "is_barcode_sticker_placeable" => true
            ],
            // ESLO
            [
                "category_name" => $groupCategorySample->getGroupCategorySampleData("3D_PRINTER"),
                "is_consumable" => false,
                "is_barcode_sticker_placeable" => true
            ],
            [
                "category_name" => $groupCategorySample->getGroupCategorySampleData("MICROSCOPE"),
                "is_consumable" => false,
                "is_barcode_sticker_placeable" => true
            ],
            [
                "category_name" => $groupCategorySample->getGroupCategorySampleData("VOLTAGE_TESTER"),
                "is_consumable" => false,
                "is_barcode_sticker_placeable" => true
            ],



            // CONSUMABLES
            [
                "category_name" => $groupCategorySample->getGroupCategorySampleData("LAN_CABLING_TOOL"),
                "is_consumable" => false,
                "is_barcode_sticker_placeable" => false
            ],
            [
                "category_name" => $groupCategorySample->getGroupCategorySampleData("PAPER"),
                "is_consumable" => true,
                "is_barcode_sticker_placeable" => false
            ],
            [
                "category_name" => $groupCategorySample->getGroupCategorySampleData("PENS"),
                "is_consumable" => true,
                "is_barcode_sticker_placeable" => false
            ],
            [
                "category_name" => $groupCategorySample->getGroupCategorySampleData("PENCIL"),
                "is_consumable" => true,
                "is_barcode_sticker_placeable" => false
            ],
        ];

        foreach ($group_category as $category) {
            ItemGroupCategory::create($category);
        }
    }
}
