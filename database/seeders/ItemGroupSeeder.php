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
                "group_category_id" => "714ef76d-7894-48ff-b0c6-7b96d4d915fd",
                "department_id" => "97c602cc-4750-4513-a65e-ecd7c38512c7",
            ],
            [
                "model_name" => "MacBook Air M1",
                "is_required_supervisor_approval" => true,

                //FKs:: Change ID values
                "group_category_id" => "8c28e766-f06b-4cac-aa32-fa651ddeea8a",
                "department_id" => "97c602cc-4750-4513-a65e-ecd7c38512c7",
            ],
            [
                "model_name" => "Spalding FIBA 2007",
                "is_required_supervisor_approval" => false,

                //FKs:: Change ID values
                "group_category_id" => "728b723a-ebdd-44b4-af6d-626d49c3d455",
                "department_id" => "5fc34f3e-7204-4df1-ba76-47628549dac0",
            ],
        ];
        foreach ($groups as $group) {
            ItemGroup::create($group);
        }
    }
}
