<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class UniqueItemGroupIds implements Rule
{
    public function passes($attribute, $value)
    {
        // Flatten the nested array and check if the 'id' values are unique
        $ids = collect($value)->pluck('item_group_id');
        return $ids->count() === $ids->unique()->count();
    }

    public function message()
    {
        return 'The id values within the items array must be unique.';
    }
}