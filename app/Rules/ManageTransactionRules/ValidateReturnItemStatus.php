<?php

namespace App\Rules\ManageTransactionRules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class ValidateReturnItemStatus implements Rule
{
    private $request;
    private $returnedItemStatusArray = [
        'RETURNED',
        'DAMAGED_BUT_REPAIRABLE',
        'UNREPAIRABLE',
    ];
    private $unreturnedItemStatusArray = [
        'UNRETURNED',
        'LOST'
    ];


    public function __construct($request)
    {
        $this->request = $request;
    }

    public function passes($attribute, $value)
    {
        $items = $this->request['items'];
        $invalidStatusFound = false;

        foreach ($items as $index => $item) {
            $isReturned = $item['is_returned'];
            $status = $item['item_status'];

            // Check if the item is returned and if its status is valid
            if ($isReturned && !in_array($status, $this->returnedItemStatusArray)) {
                $invalidStatusFound = true;
            }

            // Check if the item is not returned and if its status is valid
            if (!$isReturned && !in_array($status, $this->unreturnedItemStatusArray)) {
                $invalidStatusFound = true;
            }

            // Stop processing further items if an invalid status is found
            if ($invalidStatusFound) {
                return false;
            }
        }

        return true; // Return true if all item statuses are valid
    }
    public function message()
    {
        return "Invalid item status";
    }
}
