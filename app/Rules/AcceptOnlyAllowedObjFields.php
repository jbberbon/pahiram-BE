<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class AcceptOnlyAllowedObjFields implements Rule
{
    private $allowedFields;
    public function __construct($allowedFields)
    {
        $this->allowedFields = $allowedFields;
    }

    public function passes($attribute, $value)
    {
        foreach ($value as $key => $field) {
            if (!in_array($key, $this->allowedFields)) {
                return false;
            }
        }
        return true;
    }

    public function message()
    {
        return "Request object has invalid field";
    }
}