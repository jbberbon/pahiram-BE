<?php

namespace App\Services;

use App\Models\AccountStatus;
use App\Models\ApcisToken;
use App\Models\Role;
use App\Models\User;
use App\Models\UserDepartment;
use App\Utils\Constants\Statuses\ACCOUNT_STATUS;
use App\Utils\Constants\USER_ROLE;
use Illuminate\Support\Facades\Http;

class UserService
{

    //public function migrateUserDataFromApcisToPahiram(string $apcID, array $apcisTokenData): null|array
    public function storeNewUser(array $userDataFromApcis)
    {
        // Check first if APC ID Exists
        $user = User::where('apc_id', $userDataFromApcis['apc_id'])->first();

        try {
            // Store new user
            if (!$user) {
                $newUserDefaultData = self::newUserDefaultData();
                $mergedUserData = array_merge($userDataFromApcis, $newUserDefaultData);
                User::create($mergedUserData);
            }
        } catch (\Exception) {
            return [
                'status' => false,
                'error' => "Something went wrong. Try again later.",
                'method' => "POST"
            ];
        }
    }

    public function newUserDefaultData(): array
    {
        $borrower = USER_ROLE::BORROWER;
        $active = ACCOUNT_STATUS::ACTIVE;

        $role = Role::where('role', $borrower)->first();
        $accStatus = AccountStatus::where('acc_status', $active)->first();

        return [
            'user_role_id' => $role ? $role->id : null,
            'acc_status_id' => $accStatus ? $accStatus->id : null,
        ];
    }

    public function getUserDataFromApcisWithoutLogin(string $apcId, string $apcisToken)
    {
        $baseUrl = env('APCIS_URL');
        $users = env('APCIS_USERS');

        // Make the API request using the passed token
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apcisToken,
            'Accept' => 'application/json',
        ])->get($baseUrl . $users . '/' . $apcId);

        // Check if the API request was successful
        if ($response->successful() && isset($response['status']) && $response['status'] === true) {
            return $response->json('data');
        }

        // Return error in case of failure
        return [
            'status' => false,
            'error' => "Something went wrong. Try again later.",
            'method' => "POST"
        ];
    }

    public function checkIfUserExistsOnApcis(string $apcId, string $apcisToken): bool
    {
        $baseUrl = env('APCIS_URL');
        $users = env('APCIS_USERS');
        $usersExists = env('APCIS_CHECK_USERS_EXIST');

        // Make the API request using the passed token
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apcisToken,
            'Accept' => 'application/json',
        ])->get($baseUrl . $users . $usersExists . '/' . $apcId);

        // Check if the API request was successful
        if ($response->successful() && ($response['status']) && $response['status'] === true) {
            return $response->json('data');
        }

        // Return false in case of failure
        return false;
    }


    public function storeApcisTokenToDB(string $userID, array $apcisTokenData): null|array
    {
        try {
            ApcisToken::create([
                'user_id' => $userID,
                'token' => $apcisTokenData['access_token'],
                'expires_at' => $apcisTokenData['expires_at']
            ]);
        } catch (\Exception) {
            return [
                'status' => false,
                'error' => "Something went wrong with storing APCIS Token",
                'method' => "POST"
            ];
        }
        return null;
    }

    public function generateAndStorePahiramToken(
        object $user,
        string $apcisTokenExpiration
    ): string|array {
        try {
            $expiresAt = \DateTime::createFromFormat('Y-m-d H:i:s', $apcisTokenExpiration);
            $pahiramToken = $user->createToken('Pahiram-Token', ['*'], $expiresAt)->plainTextToken;
            return $pahiramToken;
        } catch (\Exception) {
            return [
                'status' => false,
                'error' => "Something went wrong with generating Pahiram Token",
                'method' => "POST"
            ];
        }
    }

    public function retrieveUserLoginData(
        object $user,
        string $pahiramToken,
        string $apcisToken,
        string $expiresAt
    ): array {
        try {
            $user = [
                'apc_id' => $user->apc_id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'role' => Role::getRoleById($user->user_role_id),
                'acc_status' => AccountStatus::getStatusById($user->acc_status_id),
                'department' => UserDepartment::getDepartmentAcronymByUserId($user->id)
            ];

            return [
                'user' => $user,
                'pahiram_token' => $pahiramToken,
                'apcis_token' => $apcisToken,
                'expires_at' => $expiresAt
            ];
        } catch (\Exception) {
            return [
                'status' => false,
                'error' => "Something went wrong with retrieving user data",
                'method' => "POST"
            ];
        }
    }

}