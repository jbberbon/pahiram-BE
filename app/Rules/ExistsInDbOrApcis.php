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
        if ($this->existsLocally($value)) {
            return true;
        }

        // Attempt to fetch user data from the external authentication server
        try {
            $userDataFromApi = $this->getUserDataFromApi($value);

            // Add new user to the local database
            User::create([...$userDataFromApi, ...NewUserDefaultData::defaultData(null)]);

            return true;
        } catch (\Exception $e) {
            // Handle exceptions that might occur during the API request
            return false;
        }
    }

    private function existsLocally($value)
    {
        return User::where('apc_id', $value)->exists();
    }

    private function getUserDataFromApi($value)
    {
        $token = request()->input('apcis_token');
        $baseUrl = env('APCIS_URL');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])
            ->get($baseUrl . 'users/' . $value);

        // Check if the API request was successful
        if ($response['status'] === true) {
            return $response->json('data');
        }

        // Handle the case where the API request was not successful
        throw new \Exception('API request failed');
    }

    public function message()
    {
        return "Endorser does not exist";
    }
}