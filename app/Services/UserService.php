<?php

namespace App\Services;

use App\Models\AccountStatus;
use App\Models\ApcisToken;
use App\Models\Role;
use App\Models\User;
use App\Models\UserDepartment;
use App\Utils\ApiResponseHandling;
use App\Utils\Constants\Statuses\ACCOUNT_STATUS;
use App\Utils\Constants\USER_ROLE;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class UserService
{
    private $apcisBaseUrl;
    private $apcisUsersUrl;
    private $apcisUsersExistsUrl;

    public function __construct()
    {
        $this->apcisBaseUrl = env('APCIS_URL');
        $this->apcisUsersUrl = env('APCIS_USERS');
        $this->apcisUsersExistsUrl = env('APCIS_CHECK_USERS_EXIST');
    }

    //public function migrateUserDataFromApcisToPahiram(string $apcID, array $apcisTokenData): null|array
    public function storeNewUser(array $userDataFromApcis): array|null
    {
        try {
            $newUserDefaultData = self::newUserDefaultData();
            $mergedUserData = array_merge($userDataFromApcis, $newUserDefaultData);
            User::create($mergedUserData);

            return null;
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

    public function retrieveUserDataFromApcisWithoutLogin(string $apcId, string $apcisToken)
    {
        // Make the API request using the passed token
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apcisToken,
            'Accept' => 'application/json',
        ])->get(
                $this->apcisBaseUrl . $this->apcisUsersUrl . '/' . $apcId
            );
        // Check if the API request was successful
        if (
            $response->successful() &&
            isset($response['status']) &&
            $response['status'] === true
        ) {
            return $response->json('data');
        }

        // Return error in case of failure
        return [
            'status' => false,
            'error' => "Something went wrong. Try again later.",
            'method' => "POST"
        ];
    }

    public function handleRetrieveUserDataFromApcisWithoutLogin(string $endorserApcId, string $apcisToken): null|array
    {
        $endorserExistsInPahiram = User::where('apc_id', $endorserApcId)->exists();
        if (!$endorserExistsInPahiram) {
            // Connect to APCIS to gather user data
            $userDataFromApcis = self::retrieveUserDataFromApcisWithoutLogin(
                apcId: $endorserApcId,
                apcisToken: $apcisToken
            );

            if (isset($userDataFromApcis['error'])) {
                return $userDataFromApcis;
            }

            $storedNewUser = self::storeNewUser(
                userDataFromApcis: $userDataFromApcis
            );

            if (isset($storedNewUser['error'])) {
                return $storedNewUser;
            }
        }
        return null;
    }

    public function retrieveUserDataFromApcisThroughLogin($loginCredentials): array|JsonResponse
    {
        $response = Http::timeout(10)
            ->post($this->apcisBaseUrl . '/login', $loginCredentials);
        $parsedResponse = json_decode($response->body(), true);

        // Handle error responses from APCIS
        // And Check the Array Fields from APCIS if they are as expected
        $apiResponse = ApiResponseHandling::handleApcisResponse(
            parsedResponse: $parsedResponse,
            responseCode: $response->status()
        );
        return $apiResponse;
    }

    public function checkIfUserExistsOnApcis(string $apcId, string $apcisToken): bool|array
    {
        // Make the API request using the passed token
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apcisToken,
            'Accept' => 'application/json',
        ])->get(
                $this->apcisBaseUrl .
                $this->apcisUsersUrl .
                $this->apcisUsersExistsUrl .
                '/' . $apcId
            );

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