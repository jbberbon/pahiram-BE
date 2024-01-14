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
                'department' => 'Buidling Maintenance Office',
                'department_acronym' => 'BMO',
                'supervisor_id' => '',
                'is_lending_office' => 1
            ],
            [
                'department' => 'Engineering and Science Laboratory Office',
                'department_acronym' => 'ESLO',
                'supervisor_id' => '',
                'is_lending_office' => 1
            ],
            [
                'department' => 'Information Technology and Resource Office',
                'department_acronym' => 'ITRO',
                'supervisor_id' => '',
                'is_lending_office' => 1
            ],
            // --------- NON LENDING
            [
                'department' => 'Purchasing and Logistics Office',
                'department_acronym' => 'PLO',
                'supervisor_id' => '',
                'is_lending_office' => 0
            ],
            [
                'department' => 'Finance and Accounting Office',
                'department_acronym' => 'FAO',
                'supervisor_id' => '',
                'is_lending_office' => 0
            ],
            [
                'department' => 'Human Resources Office',
                'department_acronym' => 'HR',
                'supervisor_id' => '',
                'is_lending_office' => 0
            ],
            [
                'department' => 'No Designation',
                'department_acronym' => 'N/A',
                'supervisor_id' => '',
                'is_lending_office' => 0
            ],
        ];
        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}
