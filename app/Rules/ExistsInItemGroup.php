<?php

namespace App\Rules;

use App\Models\ItemGroup;
use Illuminate\Contracts\Validation\Rule;

class ExistsInItemGroup implements Rule
{
    private $checkVal = null;
    public function passes($attribute, $value)
    {
        return ItemGroup::where('id', $value)->exists();
    }

    public function message(): string
    {
        return 'Item Model does not exist.';
    }
}
