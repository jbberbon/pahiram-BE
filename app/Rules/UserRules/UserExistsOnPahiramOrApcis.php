<?php

namespace App\Rules\UserRules;

use App\Models\User;
// use App\Utils\NewUserDefaultData;
use App\Services\UserService;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Http;

class UserExistsOnPahiramOrApcis implements Rule
{
    public function passes($attribute, $value)
    {
        $userExists = User::where('apc_id', $value)->first();
        // Check if APC ID exists in the local database
        if ($userExists) {
            return true;
        }

        // Check if APC ID Exists on APCIS
        $userService = new UserService();
        $apcisToken = request()->input('request_data.endorsed_by') ? request()->input('request_data.endorsed_by') : request()->input('endorsed_by');
        $userExistsOnApcis = $userService->checkIfUserExistsOnApcis(
            apcId: $value,
            apcisToken: $apcisToken
        );
        // \Log::info("USER ON APCIS", ['user_exists' => $userExistsOnApcis]);

        return $userExistsOnApcis;
    }

    public function message()
    {
        return "Endorser does not exist";
    }
}