<?php

namespace App\Services;

use App\Models\AccountStatus;
use App\Models\ApcisToken;
use App\Models\Role;
use App\Models\UserDepartment;

class AuthService
{
    public function storeApcisTokenToDB($user, array $apcisTokenData): ?\Illuminate\Http\JsonResponse
    {
        try {
            ApcisToken::create([
                'user_id' => $user->id,
                'token' => $apcisTokenData['access_token'],
                'expires_at' => $apcisTokenData['expires_at']
            ]);
        } catch (\Exception) {
            return response()->json([
                'status' => false,
                'error' => "Something went wrong with storing APCIS Token",
                'method' => "POST"
            ], 500);
        }
        return null;
    }

    public function generateAndStorePahiramToken(
        object $user,
        string $apcisTokenExpiration
    ): string|\Illuminate\Http\JsonResponse {
        try {
            $expiresAt = \DateTime::createFromFormat('Y-m-d H:i:s', $apcisTokenExpiration);
            $pahiramToken = $user->createToken('Pahiram-Token', ['*'], $expiresAt)->plainTextToken;

            return $pahiramToken;
        } catch (\Exception) {
            return response()->json([
                'status' => false,
                'error' => "Something went wrong with generating Pahiram Token",
                'method' => "POST"
            ], 500);
        }
    }

    public function retrieveUserLoginData(
        object $user,
        string $pahiramToken,
        string $apcisToken,
        string $expiresAt
    ): array|\Illuminate\Http\JsonResponse {
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
            return response()->json([
                'status' => false,
                'error' => "Something went wrong with generating Pahiram Token",
                'method' => "POST"
            ], 500);
        }
    }
}