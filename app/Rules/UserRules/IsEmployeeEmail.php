<?php

namespace App\Rules\UserRules;

use Illuminate\Contracts\Validation\Rule;

class IsEmployeeEmail implements Rule
{
    public function passes($attribute, $value)
    {
        // Check for employee email
        $user = auth()->user();
        return str_ends_with($user->email, '@apc.edu.ph');
    }

    public function message()
    {
        return 'Unauthorized access';
    }
}