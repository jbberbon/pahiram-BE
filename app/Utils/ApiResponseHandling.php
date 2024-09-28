<?php

namespace App\Utils;

class ApiResponseHandling
{
    public static function handleApcisResponse(array|null $parsedResponse, int $responseCode): null|array
    {
        $response = [
            'status' => false,
            'error' => 'Unexpected auth server response.',
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

        // Handle 200 response and validate its structure
        if ($responseCode === 200) {
            if (self::isValidResponseStructure($parsedResponse) && self::hasRequiredFields($parsedResponse)) {
                return null; // Valid response
            } else {
                return $response; // Invalid structure
            }
        }

        return null;
    }

    private static function isValidResponseStructure(array $response): bool
    {
        // Ensure 'data' is set and is an array before checking its contents
        return isset($response['data'])
            && is_array($response['data'])
            && isset($response['data']['user'], $response['data']['apcis_token'])
            && isset($response['data']['user']['apc_id'], $response['data']['user']['first_name'], $response['data']['user']['last_name'], $response['data']['user']['email'])
            && isset($response['data']['apcis_token']['access_token'], $response['data']['apcis_token']['expires_at']);
    }

    public static function hasRequiredFields(array $response): bool
    {
        $user = $response['data']['user'];
        $token = $response['data']['apcis_token'];
        return !empty($user['apc_id']) &&
            !empty($user['first_name']) &&
            !empty($user['last_name']) &&
            !empty($user['email']) &&
            !empty($token['access_token']) &&
            !empty($token['expires_at']);
    }
}