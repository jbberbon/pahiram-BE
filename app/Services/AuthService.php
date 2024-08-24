<?php

namespace App\Services;

use App\Models\AccountStatus;
use App\Models\ApcisToken;
use App\Models\Role;
use App\Models\UserDepartment;

class AuthService
{
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