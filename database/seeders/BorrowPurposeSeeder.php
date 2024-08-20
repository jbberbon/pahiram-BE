<?php

namespace Database\Seeders;

use App\Models\BorrowPurpose;
use App\Utils\Constants\Statuses\BORROW_PURPOSE;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BorrowPurposeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $purposes = BORROW_PURPOSE::PURPOSE_ARRAY;
        foreach ($purposes as $purpose) {
            BorrowPurpose::create($purpose);
        }
    }
}
