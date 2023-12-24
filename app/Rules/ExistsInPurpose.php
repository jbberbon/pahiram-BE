<?php

namespace App\Rules;

use App\Models\BorrowPurpose;
use Illuminate\Contracts\Validation\Rule;

class ExistsInPurpose implements Rule
{
    private $checkVal = null;
    public function passes($attribute, $value)
    {
        return BorrowPurpose::where('id', $value)->exists();
    }

    public function message(): string
    {
        return 'Item does not exist.';
    }
}
