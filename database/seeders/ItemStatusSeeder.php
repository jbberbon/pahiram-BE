<?php

namespace Database\Seeders;

use App\Models\ItemStatus;
use App\Utils\Constants\Statuses\ITEM_STATUS;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItemStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $item_status = ITEM_STATUS::ITEM_STATUS_ARRAY;

        foreach ($item_status as $status) {
            ItemStatus::create($status);
        }
        ;
    }
}
