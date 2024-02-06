<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class AtLeastOneFieldPresent implements Rule
{
    public function passes($attribute, $value)
    {
        // Check if at least one of the fields is present
        return !empty($value['request_data']) || !empty($value['edit_existing_items']) || !empty($value['add_new_items']);
    }

    public function message()
    {
        return 'At least one of the fields (request_data, edit_existing_items, add_new_items) must be present.';
    }
}
