<?php

namespace App\Rules;

use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

class ExistsInUsers implements Rule
{
    private $checkVal = null;
    public function passes($attribute, $value)
    {
        return User::where('id', $value)->exists();
    }

    public function message(): string
    {
        return 'User does not exist.';
    }
}
