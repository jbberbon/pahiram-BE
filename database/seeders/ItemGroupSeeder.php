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
                "group_category_id" => "a0331f53-5af3-4ad7-a31e-8c1a424f5b89",
                "department_id" => "9c8a30a9-596b-4edf-b6a4-4d1810551838",
            ],
            [
                "model_name" => "MacBook Air M1",
                "is_required_supervisor_approval" => true,

                //FKs:: Change ID values
                "group_category_id" => "2637d107-18b7-445f-917b-373e4c904cfe",
                "department_id" => "9c8a30a9-596b-4edf-b6a4-4d1810551838",
            ],
            [
                "model_name" => "Spalding FIBA 2007",
                "is_required_supervisor_approval" => false,

                //FKs:: Change ID values
                "group_category_id" => "3777f273-167c-4b61-aabb-8f43b7349fc2",
                "department_id" => "4cdebdc5-7c74-4cca-80b7-c27b2c2c91d0",
            ],
        ];
        foreach ($groups as $group) {
            ItemGroup::create($group);
        }
    }
}
