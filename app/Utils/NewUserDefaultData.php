<?php

namespace App\Utils;

use App\Models\AccountStatus;
use App\Models\Course;
use App\Models\Department;
use App\Models\Role;

class NewUserDefaultData
{
    public static function defaultData($course): array
    {
        $role = Role::where('role', 'BORROWER')->first();
        $accStatus = AccountStatus::where('acc_status', 'ACTIVE')->first();
        $department = Department::where('department_acronym', 'N/A')->first();

        if(!$course) {
            $course = Course::where('course_acronym', 'N/A')->firstOrFail();
        }

        return [
            'user_role_id' => $role ? $role->id : null,
            'course_id' => $course->id,
            'acc_status_id' => $accStatus ? $accStatus->id : null,
            'department_id' => $department ? $department->id : null,
        ];
    }
}