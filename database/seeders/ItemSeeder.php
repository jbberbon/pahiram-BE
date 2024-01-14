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
                'item_group_id' => '3b15d420-cd26-4940-a906-efac70c50457',
                'item_status_id' => '1d80bb6c-1d80-4935-b6e0-cc2bbb650522',
            ],
            [
                'apc_item_id' => '000002',
                'item_group_id' => '3b15d420-cd26-4940-a906-efac70c50457',
                'item_status_id' => '0303d921-2e93-4af5-b3e3-4400f347a8e9',
            ],
            [
                'apc_item_id' => '000003',
                'item_group_id' => 'ea31c70d-ec49-4dfc-abf4-e38f64cec040',
                'item_status_id' => '1d80bb6c-1d80-4935-b6e0-cc2bbb650522',
            ],
            [
                'apc_item_id' => '000004',
                'item_group_id' => 'ea31c70d-ec49-4dfc-abf4-e38f64cec040',
                'item_status_id' => '1d80bb6c-1d80-4935-b6e0-cc2bbb650522',
            ],
            [
                'apc_item_id' => '000005',
                'item_group_id' => 'ea31c70d-ec49-4dfc-abf4-e38f64cec040',
                'item_status_id' => 'b9f886d9-6c52-4893-b739-1209bcb3d108',
            ],
            [
                'apc_item_id' => '000006',
                'item_group_id' => '5b7f02d4-269e-4774-9fe5-11e39930e82b',
                'item_status_id' => '1d80bb6c-1d80-4935-b6e0-cc2bbb650522',
            ],
        ];
        foreach ($items as $item) {
            Item::create($item);
        }
    }
}
