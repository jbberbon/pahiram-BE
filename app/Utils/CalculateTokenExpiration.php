<?php

namespace App\Utils;

class CalculateTokenExpiration
{
    public static function calculateExpiration($rememberMe)
    {
        $expiration = $rememberMe ? now()->addDays(7) : now()->addDay();

        return $expiration;
    }
}