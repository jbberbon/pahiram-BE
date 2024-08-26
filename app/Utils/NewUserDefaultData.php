<?php

namespace App\Utils;

use App\Models\AccountStatus;
use App\Models\Course;
use App\Models\Role;
use App\Utils\Constants\Statuses\ACCOUNT_STATUS;
use App\Utils\Constants\USER_ROLE;

class NewUserDefaultData
{
    public static function defaultData($course): array
    {
        $borrower = USER_ROLE::BORROWER;
        $active = ACCOUNT_STATUS::ACTIVE;

        $role = Role::where('role', $borrower)->first();
        $accStatus = AccountStatus::where('acc_status', $active)->first();

        return [
            'user_role_id' => $role ? $role->id : null,
            'acc_status_id' => $accStatus ? $accStatus->id : null,
        ];
    }

    public static function newUserDefaultData(): array
    {
        $borrower = USER_ROLE::BORROWER;
        $active = ACCOUNT_STATUS::ACTIVE;

        $role = Role::where('role', $borrower)->first();
        $accStatus = AccountStatus::where('acc_status', $active)->first();

        return [
            'user_role_id' => $role ? $role->id : null,
            'acc_status_id' => $accStatus ? $accStatus->id : null,
        ];
    }
}