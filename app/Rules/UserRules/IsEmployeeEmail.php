<?php

namespace App\Rules\UserRules;

use Illuminate\Contracts\Validation\Rule;

class IsEmployeeEmail implements Rule
{
    public function passes($attribute, $value)
    {
        // Check for employee email
        $user = auth()->user();
        // \Illuminate\Support\Facades\Log::error('User Data: ' . json_encode($user->toArray()));
        return str_ends_with($user->email, '@apc.edu.ph');
    }

    public function message()
    {
        return 'Unauthorized access';
    }
}