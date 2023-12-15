<?php

namespace App\Utils;

use App\Models\AccountStatus;
use App\Models\Department;
use App\Models\Role;

class NewUserDefaultData
{
    public static function defaultData($course): array
    {
        $role = Role::where('role_code', 1010)->first();
        $accStatus = AccountStatus::where('acc_status_code', 1010)->first();
        $department = Department::where('department_code', 1010)->first();

        return [
            'is_admin' => 0,
            'user_role_id' => $role ? $role->id : null,
            'course_id' => $course ? $course->id : null,
            'acc_status_id' => $accStatus ? $accStatus->id : null,
            'department_id' => $department ? $department->id : null,
        ];
    }
}