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
                'supervisor_id' => ''
            ],
            [
                'department' => 'Buidling Maintenance Office',
                'department_code' => 2020,
                'department_acronym' => 'BMO',
                'supervisor_id' => ''
            ],
            [
                'department' => 'Engineering and Science Laboratory Office',
                'department_code' => 3030,
                'department_acronym' => 'ESLO',
                'supervisor_id' => ''
            ],
            [
                'department' => 'Information Technology and Resource Office',
                'department_code' => 4040,
                'department_acronym' => 'ITRO',
                'supervisor_id' => ''
            ],
            [
                'department' => 'Finance and Accounting Office',
                'department_code' => 5050,
                'department_acronym' => 'FAO',
                'supervisor_id' => ''
            ],
            [
                'department' => 'Human Resources Office',
                'department_code' => 6060,
                'department_acronym' => 'HR',
                'supervisor_id' => ''
            ],
        ];
        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}
