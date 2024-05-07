<?php

namespace Database\Seeders;

use App\Models\ItemGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItemGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = [
            [
                "model_name" => "Canon 200d",
                "is_required_supervisor_approval" => true,

                //FKs:: Change ID values
                "group_category_id" => "ba881ae9-9165-414a-8d78-95d95337f021",
                "department_id" => "3b3902ba-1a70-4928-a7d0-4ce1e897929f",
            ],
            [
                "model_name" => "MacBook Air M1",
                "is_required_supervisor_approval" => true,

                //FKs:: Change ID values
                "group_category_id" => "beb92f9f-e629-48d2-954f-0e23eff99a94",
                "department_id" => "3b3902ba-1a70-4928-a7d0-4ce1e897929f",
            ],
            [
                "model_name" => "Spalding FIBA 2007",
                "is_required_supervisor_approval" => false,

                //FKs:: Change ID values
                "group_category_id" => "ba881ae9-9165-414a-8d78-95d95337f021",
                "department_id" => "3b3902ba-1a70-4928-a7d0-4ce1e897929f",
            ],
        ];
        foreach ($groups as $group) {
            ItemGroup::create($group);
        }
    }
}
