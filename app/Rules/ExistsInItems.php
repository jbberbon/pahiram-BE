<?php

namespace App\Rules;

use App\Models\Item;
use Illuminate\Contracts\Validation\Rule;

class ExistsInItems implements Rule
{
    private $checkVal = null;
    public function passes($attribute, $value)
    {
        return Item::where('id', $value)->exists();
    }

    public function message(): string
    {
        return 'Item does not exist.';
    }
}
