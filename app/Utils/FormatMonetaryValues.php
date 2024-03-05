<?php

namespace App\Utils;

class FormatMonetaryValues
{
    public static function formatValue($amount)
    {
        // Check if the input is null or not numeric
        if ($amount === null || !is_numeric($amount)) {
            return "Invalid input";
        }

        // Format the number with commas and peso sign
        $formattedAmount = number_format($amount, 2, '.', ',');
        $formattedAmount = '₱' . $formattedAmount;

        return $formattedAmount;
    }

}