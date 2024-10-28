<?php

namespace Database\Seeders\Dev;

use App\Models\AccountStatus;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Models\UserDepartment;
use App\Utils\Constants\OFFICE_LIST;
use App\Utils\Constants\Statuses\ACCOUNT_STATUS;
use App\Utils\Constants\USER_ROLE;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $borrower = Role::getIdByRole(USER_ROLE::BORROWER);
        $supervisor = Role::getIdByRole(USER_ROLE::SUPERVISOR);
        $coSupervisor = Role::getIdByRole(USER_ROLE::COSUPERVISOR);
        $lendingEmp = Role::getIdByRole(USER_ROLE::LENDING_EMPLOYEE);
        $penaltyManager = Role::getIdByRole(USER_ROLE::PENALTY_MANAGER);

        $activeStatus = AccountStatus::getIdByStatus(ACCOUNT_STATUS::ACTIVE);
        $users = [
            [
                'first_name' => 'John Christian',
                'last_name' => 'Berbon',
                'apc_id' => '2021-140966',
                'email' => 'jbberbon@student.apc.edu.ph',
                'user_role_id' => $borrower,
                'acc_status_id' => $activeStatus
            ],

            // ITRO
            [
                'first_name' => 'Jojo',
                'last_name' => 'De Jesus',
                'apc_id' => '2021-000001',
                'email' => 'jdjesus@apc.edu.ph',
                'user_role_id' => $supervisor,
                'acc_status_id' => $activeStatus
            ],
            [
                'first_name' => 'Ryan John',
                'last_name' => 'Pascual',
                'apc_id' => '2021-000002',
                'email' => 'rjpascual@apc.edu.ph',
                'user_role_id' => $coSupervisor,
                'acc_status_id' => $activeStatus
            ],
            [
                'first_name' => 'Josh',
                'last_name' => 'Masotes',
                'apc_id' => '2021-000003',
                'email' => 'jmasotes@apc.edu.ph',
                'user_role_id' => $lendingEmp,
                'acc_status_id' => $activeStatus
            ],

            // FINANCE
            [
                'first_name' => 'Tricia',
                'last_name' => 'Bermillo',
                'apc_id' => '2021-000013',
                'email' => 'trbermillo@apc.edu.ph',
                'user_role_id' => $supervisor,
                'acc_status_id' => $activeStatus
            ],
            [
                'first_name' => 'Rigor',
                'last_name' => 'Calleja',
                'apc_id' => '2021-000014',
                'email' => 'rcalleja@apc.edu.ph',
                'user_role_id' => $coSupervisor,
                'acc_status_id' => $activeStatus
            ],
            [
                'first_name' => 'Ansen',
                'last_name' => 'Dolz',
                'apc_id' => '2021-000015',
                'email' => 'andolz@apc.edu.ph',
                'user_role_id' => $penaltyManager,
                'acc_status_id' => $activeStatus
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }


        // Assign users to Office
        $itro = Department::getIdBasedOnAcronym(acronym: OFFICE_LIST::ITRO);
        $fao = Department::getIdBasedOnAcronym(acronym: OFFICE_LIST::FAO);

        $userDepartments = [
            [
                'user_id' => User::where('email', 'jdjesus@apc.edu.ph')->first()->id,
                'department_id' => $itro
            ],
            [
                'user_id' => User::where('email', 'rjpascual@apc.edu.ph')->first()->id,
                'department_id' => $itro
            ],
            [
                'user_id' => User::where('email', 'jmasotes@apc.edu.ph')->first()->id,
                'department_id' => $itro
            ],

            [
                'user_id' => User::where('email', 'trbermillo@apc.edu.ph')->first()->id,
                'department_id' => $fao
            ],
            [
                'user_id' => User::where('email', 'rcalleja@apc.edu.ph')->first()->id,
                'department_id' => $fao
            ],
            [
                'user_id' => User::where('email', 'andolz@apc.edu.ph')->first()->id,
                'department_id' => $fao
            ],
        ];

        foreach ($userDepartments as $userDepartment) {
            UserDepartment::create($userDepartment);
        }
    }
}
