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
                'apc_item_id' => '000001',
                'item_group_id' => '7147eed4-6065-4013-8def-7b11dd729bd6',
                'item_status_id' => '22e3817c-1882-4a0c-ae21-60c16802085d',
            ],
            [
                'apc_item_id' => '000002',
                'item_group_id' => '7147eed4-6065-4013-8def-7b11dd729bd6',
                'item_status_id' => '22e3817c-1882-4a0c-ae21-60c16802085d',
            ],
            [
                'apc_item_id' => '000003',
                'item_group_id' => '9e009126-99a6-46be-8236-7718554748ac',
                'item_status_id' => '22e3817c-1882-4a0c-ae21-60c16802085d',
            ],
            [
                'apc_item_id' => '000004',
                'item_group_id' => '9e009126-99a6-46be-8236-7718554748ac',
                'item_status_id' => '6309da34-c745-4d72-9fb0-ccc6fc5b6eae',
            ],
            [
                'apc_item_id' => '000005',
                'item_group_id' => '835532a8-a6a6-4991-9c22-911954ba374f',
                'item_status_id' => '22e3817c-1882-4a0c-ae21-60c16802085d',
            ],
            [
                'apc_item_id' => '000006',
                'item_group_id' => '835532a8-a6a6-4991-9c22-911954ba374f',
                'item_status_id' => '22e3817c-1882-4a0c-ae21-60c16802085d',
            ],
        ];
        foreach ($items as $item) {
            Item::create($item);
        }
    }
}
