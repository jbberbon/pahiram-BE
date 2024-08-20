<?php

namespace Database\Seeders;

use App\Models\BorrowedItemStatus;
use App\Utils\Constants\Statuses\BORROWED_ITEM_STATUS;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BorrowedItemStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $borrowed_item_status = BORROWED_ITEM_STATUS::BORROWED_ITEM_STATUS_ARRAY;
        foreach ($borrowed_item_status as $status) {
            BorrowedItemStatus::create($status);
        }
    }
}
