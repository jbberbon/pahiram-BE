<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'department' => 'N/A',
                'department_code' => 1010,
                'department_acronym' => 'N/A',
                'supervisor_id' => '',
                'is_lending_office' => 0
            ],
            [
                'department' => 'Purchasing and Logistics Office',
                'department_code' => 2020,
                'department_acronym' => 'PLO',
                'supervisor_id' => '',
                'is_lending_office' => 0
            ],
            [
                'department' => 'Buidling Maintenance Office',
                'department_code' => 3030,
                'department_acronym' => 'BMO',
                'supervisor_id' => '',
                'is_lending_office' => 1
            ],
            [
                'department' => 'Engineering and Science Laboratory Office',
                'department_code' => 4040,
                'department_acronym' => 'ESLO',
                'supervisor_id' => '',
                'is_lending_office' => 1
            ],
            [
                'department' => 'Information Technology and Resource Office',
                'department_code' => 5050,
                'department_acronym' => 'ITRO',
                'supervisor_id' => '',
                'is_lending_office' => 1
            ],
            [
                'department' => 'Finance and Accounting Office',
                'department_code' => 6060,
                'department_acronym' => 'Finance',
                'supervisor_id' => '',
                'is_lending_office' => 0
            ],
            [
                'department' => 'Human Resources Office',
                'department_code' => 7070,
                'department_acronym' => 'HR',
                'supervisor_id' => '',
                'is_lending_office' => 0
            ],
        ];
        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}
