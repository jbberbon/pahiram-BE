<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CheckArraySize implements Rule
{
    /**
     * This Rule Checks if the request is array,
     * and ONLY accepts 3 element sized array
     */
    private $checkVal = null;
    public function passes($attribute, $value)
    {
        // Ensure it has exactly three elements
        if (count($value) !== 3) {
            return false;
        }
        return true;
    }

    public function message(): string
    {
        return 'The items array must be an array with exactly three elements.';
    }
}
