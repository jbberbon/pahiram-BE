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
                "total_quantity" => 2,
                "available_quantity"  => 3,

                //FKs
                "group_category_id" => "",
                "department_id" => "",
            ],
            [
                "model_name" => "Spalding x86",
                "is_required_supervisor_approval" => false,
                "total_quantity" => 10,
                "available_quantity"  => 10,

                //FKs
                "group_category_id" => "",
                "department_id" => "",
            ],
        ];
        foreach ($groups as $group) {
            ItemGroup::create($group);
        }
    }
}
