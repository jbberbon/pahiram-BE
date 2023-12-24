<?php

namespace App\Rules;

use App\Models\Department;
use Illuminate\Contracts\Validation\Rule;

class ExistsInDepartment implements Rule
{
    private $checkVal = null;
    public function passes($attribute, $value)
    {
        return Department::where('id', $value)->exists();
    }

    public function message(): string
    {
        return 'User does not exist.';
    }
}
