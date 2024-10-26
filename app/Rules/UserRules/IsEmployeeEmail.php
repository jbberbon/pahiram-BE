<?php

namespace App\Rules\UserRules;

use Auth;
use Illuminate\Contracts\Validation\Rule;

class IsEmployeeEmail implements Rule
{
    public function passes($attribute, $value)
    {
        // Ensure there is an authenticated user
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        // Check if the value (email) ends with '@apc.edu.ph'
        return str_ends_with($user->email, '@apc.edu.ph');
    }

    public function message()
    {
        return 'Unauthorized access: user email is not for apc employee';
    }
}