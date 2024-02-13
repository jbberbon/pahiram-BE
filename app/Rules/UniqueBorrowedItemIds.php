<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class UniqueBorrowedItemIds implements Rule
{
    public function passes($attribute, $value)
    {
        // Flatten the nested array and check if the 'id' values are unique
        $ids = collect($value)->pluck('borrowed_item_id');
        return $ids->count() === $ids->unique()->count();
    }

    public function message()
    {
        return 'The id values must be unique.';
    }
}