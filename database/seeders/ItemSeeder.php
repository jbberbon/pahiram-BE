<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'apc_item_id' => "0002-XXX",
                'item_group_id' => "6e4a900e-3307-4944-bf38-2a13955cd6f5"
            ]
        ];
        foreach ($items as $item) {
            Item::create($item);
        }
    }
}
