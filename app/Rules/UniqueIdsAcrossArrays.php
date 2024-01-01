<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class UniqueIdsAcrossArrays implements Rule
{
    protected $requestData;

    public function __construct($requestData)
    {
        $this->requestData = $requestData;
    }

    public function passes($attribute, $value)
    {
        // Extract the 'item_group_id' values from both arrays
        $editIds = collect($this->requestData['edit_existing_items'])->pluck('item_group_id');
        $newIds = collect($this->requestData['add_new_items'])->pluck('item_group_id');

        // Merge and check if the 'item_group_id' values are unique across both arrays
        $allIds = $editIds->merge($newIds);

        return $allIds->count() === $allIds->unique()->count();
    }

    public function message()
    {
        return 'The item_group_id values across both arrays must be unique.';
    }
}
