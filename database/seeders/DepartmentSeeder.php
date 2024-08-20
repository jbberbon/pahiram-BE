<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Utils\Constants\OFFICE_LIST;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = OFFICE_LIST::OFFICE_ARRAY;
        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}
