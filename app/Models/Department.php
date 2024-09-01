<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        'department',
        'department_code',
        'department_acronym',
        'supervisor_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public static function getDepartmentBasedOnId($departmentId)
    {
        $department = self::where('id', $departmentId)->first();

        return $department ? $department->department . ' ' . '(' . $department->department_acronym . ')' : null;
    }

    public static function getAcronymById($departmentId)
    {
        $department = self::where('id', $departmentId)->first();
        return $department ? $department->department_acronym : null;
    }

    public static function getIdBasedOnAcronym(string $acronym)
    {
        $department = self::where('department_acronym', $acronym)->first();

        return $department ? $department->id : null;
    }
}
