<?php

namespace App\Models;

use App\Traits\UserIdExistsTrait;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDepartment extends Model
{
    use HasFactory, Uuids, UserIdExistsTrait;

    protected $table = 'user_departments';

    protected $fillable = [
        'user_id',
        'department_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public static function getDepartmentAcronymByUserId(string $userId): string|null
    {
        $userDepartment = self::where('user_id', $userId)->first();
        if (!$userDepartment) {
            return null;
        }

        $departmentAcronym = Department::getAcronymById($userDepartment->department_id);

        return $departmentAcronym;
    }
}
