<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
                // Status
            AccountStatusSeeder::class,
            BorrowedItemStatusSeeder::class,
            PenalizedTransactionStatusesSeeder::class,
            BorrowTransactionStatusSeeder::class,
            ItemStatusSeeder::class,

                // Non Statuses
            BorrowPurposeSeeder::class,
            DepartmentSeeder::class,
            RoleSeeder::class,

                // NEED FK
            ItemGroupCategorySeeder::class,
            ItemGroupSeeder::class,
            ItemSeeder::class,
        ]);
    }
}
