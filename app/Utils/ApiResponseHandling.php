<?php

namespace App\Utils;

class ApiResponseHandling
{
    public static function handleApcisResponse(array $parsedResponse, int $responseCode)
    {
        // Check if the response status is not 200 (OK) 
        // and there's no 'status' field in the API response
        if ($responseCode !== 200 && !isset($parsedResponse['status'])) {
            return response()->json([
                'status' => false,
                'error' => 'Unexpected API response: ' . $responseCode,
                'method' => 'POST'
            ], $responseCode);
        }

        // Check if the 'status' field is present and set to false
        if (isset($parsedResponse['status']) && $parsedResponse['status'] === false) {
            return response($parsedResponse, 401);
        }

        // No return value, just continue with the process
    }
}