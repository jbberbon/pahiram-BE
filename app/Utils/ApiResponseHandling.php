<?php

namespace App\Utils;

class ApiResponseHandling
{
    public static function handleApcisResponse(array|null $parsedResponse, int $responseCode): null|array
    {
        $response = [
            'status' => false,
            'error' => 'Unexpected API response',
            'method' => 'POST'
        ];

        if ($responseCode === 401) {
            return $parsedResponse;
        }

        // Check if the response is null
        if (is_null($parsedResponse)) {
            return $response;
        }

        // Check if the response status is not 200 (OK) 
        // and there's no 'status' field in the API response
        if ($responseCode !== 200 && !isset($parsedResponse['status'])) {
            return $response;
        }

        // Check if the 'status' field is present and set to false
        if (isset($parsedResponse['status']) && $parsedResponse['status'] === false) {
            return $response;
        }

        return null;
    }
}