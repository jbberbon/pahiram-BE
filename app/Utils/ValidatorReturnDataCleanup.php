<?php

namespace App\Utils;

class ValidatorReturnDataCleanup
{
    public static function cleanup($errorObject)
    {
        $cleanData = [];
        foreach ($errorObject as $fieldName => $errors) {
            $cleanData[$fieldName] = $errors[0];
        }
        return $cleanData;
    }
}