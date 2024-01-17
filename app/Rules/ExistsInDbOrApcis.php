<?php

namespace App\Rules;

use App\Models\User;
use App\Utils\NewUserDefaultData;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExistsInDbOrApcis implements Rule
{
    public function passes($attribute, $value)
    {
        $userExists = User::where('apc_id', $value)->exists();
        
        // Check if the value is unique in the local database
        if ($userExists) {
            return true;
        }

        // Attempt to fetch user data from the external authentication server
        try {
            $userDataFromApi = $this->getUserDataFromApi($value);
            Log::info($userDataFromApi);

            $newUser = array_merge($userDataFromApi, NewUserDefaultData::defaultData(null));

            // Add new user to the local database
            User::create($newUser);

            return true;
        } catch (\Exception $e) {
            // Handle exceptions that might occur during the API request
            return false;
        }
    }

    private function getUserDataFromApi($value)
    {

        $token = request()->input('apcis_token');
        $baseUrl = env('APCIS_URL');
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])
            ->get($baseUrl . 'users/' . $value);

        // Check if the API request was successful
        if ($response['status'] === true) {
            return $response->json('data');
        }
        return false;
    }

    public function message()
    {
        return "Endorser does not exist";
    }
}