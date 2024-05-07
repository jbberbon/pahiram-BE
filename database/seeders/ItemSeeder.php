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
                'item_group_id' => '1c10af39-d8e2-46a7-9fd5-28352694d031',
                'item_status_id' => '58fb917b-191c-46c5-a56f-6e3ed8164808',
            ],
            [
                'apc_item_id' => '000002',
                'item_group_id' => '1c10af39-d8e2-46a7-9fd5-28352694d031',
                'item_status_id' => '58fb917b-191c-46c5-a56f-6e3ed8164808',
            ],
            [
                'apc_item_id' => '000003',
                'item_group_id' => '34ada0a7-82a7-43a7-bbea-9feae8db4c08',
                'item_status_id' => '58fb917b-191c-46c5-a56f-6e3ed8164808',
            ],
            [
                'apc_item_id' => '000004',
                'item_group_id' => '34ada0a7-82a7-43a7-bbea-9feae8db4c08',
                'item_status_id' => '58fb917b-191c-46c5-a56f-6e3ed8164808',
            ],
            [
                'apc_item_id' => '000005',
                'item_group_id' => '6b629202-1fab-4b3e-9fed-a001244bd659',
                'item_status_id' => '58fb917b-191c-46c5-a56f-6e3ed8164808',
            ],
            [
                'apc_item_id' => '000006',
                'item_group_id' => '6b629202-1fab-4b3e-9fed-a001244bd659',
                'item_status_id' => '58fb917b-191c-46c5-a56f-6e3ed8164808',
            ],
        ];
        foreach ($items as $item) {
            Item::create($item);
        }
    }
}
