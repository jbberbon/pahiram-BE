<?php

namespace App\Rules;

use App\Models\User;
use App\Utils\NewUserDefaultData;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Http;

class ExistsInDbOrApcis implements Rule
{
    public function passes($attribute, $value)
    {
        // Check if the value is unique in the local database
        $existsInDb = User::where('apc_id', $value)->exists();


        if (!$existsInDb) {
            try {
                // Get the token from the 'apcis_token' field
                $token = request()->input('apcis_token');
                $headers = [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json'
                ];
                $baseUrl = env('APCIS_URL');
                $response = Http::withHeaders($headers)
                    ->get($baseUrl . 'users/' . $value);

                // Check if the API request was successful
                if ($response['status'] === true) {
                    // Add new user to the Pahiram db
                    $userDataFromApi = $response->json('data');
                    $newUser = [
                        ...$userDataFromApi,
                        ...NewUserDefaultData::defaultData(null)
                    ];
                    User::create($newUser);

                    return true;
                }

                // Handle the case where the API request was not successful
                // return false;
            } catch (\Exception $e) {
                // Handle exceptions that might occur during the API request
                return false;
            }
        }
        // If it exists locally, no need to check the API
        return true;
    }


    public function message()
    {
        return 'The provided apc_id does not exist on the external authentication server.';
    }
}
