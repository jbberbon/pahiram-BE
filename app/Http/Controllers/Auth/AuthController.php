<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\AccountStatus;
use App\Models\ApcisToken;
use App\Models\Role;
use App\Models\SystemAdmin;
use App\Models\User;
use App\Models\UserDepartment;
use App\Services\AuthService;
use App\Utils\ApiResponseHandling;
use App\Utils\NewUserDefaultData;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    private $systemAdmin;

    private $course;

    public function __construct(SystemAdmin $systemAdmin)
    {
        $this->systemAdmin = $systemAdmin;
    }

    /**
     * Login Method
     */
    public function login(LoginRequest $request)
    {
        $validatedData = $request->validated();
        try {
            /**
             * 1. Access APCIS login API
             */
            $dummyApcisUrl = env('APCIS_URL');
            $response = Http::timeout(10)->post($dummyApcisUrl . '/login', $validatedData);
            $parsedResponse = json_decode($response->body(), true);

            // Handle error responses from APCIS
            $apiResponse = ApiResponseHandling::handleApcisResponse($parsedResponse, $response->status());
            if ($apiResponse !== null) {
                return response()->json($apiResponse, $response->status());
            }

            // Continue because APCIS Request is success
            $parsedUserData = $parsedResponse['data']['user'];
            $parsedToken = $parsedResponse['data']['apcis_token'];

            /**
             * 4. Check USER if already exist in pahiram-BE Database
             */
            $user = User::where('apc_id', $parsedUserData['apc_id'])->first();

            // Does NOT exist yet, add user to db
            if (!$user) {
                $defaultData = NewUserDefaultData::newUserDefaultData();
                $mergedUserData = array_merge($parsedUserData, $defaultData);
                $user = User::create($mergedUserData);
            }

            /**
             * 5. Store APCIS token to Pahiram DB
             */
            $authService = new AuthService();
            $isApcisStored = $authService->storeApcisTokenToDB($user->id, $parsedToken);
            if ($isApcisStored) {
                return response()->json($isApcisStored, 500);
            }

            /**
             * 6. Generate and store Pahiram Token 
             *     with SAME expiration as APCIS
             */
            $pahiramToken = $authService->generateAndStorePahiramToken($user, $parsedToken['expires_at']);
            if (is_array($pahiramToken)) {
                return response()->json($pahiramToken, 500);
            }

            /**
             * 7. Prepare Return Data
             */
            $returnData = $authService->retrieveUserLoginData(
                $user,
                $pahiramToken,
                $parsedToken['access_token'],
                $parsedToken['expires_at']
            );
            if (isset($returnData['error'])) {
                return response()->json($returnData, 500);
            }

            return response()->json([
                "status" => true,
                "data" => $returnData,
                'method' => 'POST'
            ], 200);

        } catch (RequestException $exception) {
            // Handle HTTP request exception
            // \Log::error('API Request Failed:', ['exception' => $exception->getMessage()]);
            return response()->json([
                'status' => false,
                'error' => 'APCIS API login request failed',
                'method' => 'POST'
            ], 500);
        } catch (\Exception $exception) {
            // Handle other exceptions
            // \Log::error('Unexpected Exception:', ['exception' => $exception->getMessage()]);
            return response()->json([
                'status' => false,
                'error' => 'Unexpected error',
                'method' => 'POST'
            ], 500);
        }
    }

    /**
     * Logout current session.
     */
    public function logout(Request $request)
    {
        try {
            $currentToken = $request->user()->currentAccessToken();
            $currentToken->delete();

            return response()->json([
                'status' => true,
                'message' => 'Logged out',
                'method' => 'DELETE'
            ], 200);
        } catch (\Exception) {
            return response()->json([
                'status' => false,
                'error' => 'Unexpected logout error',
                'method' => 'DELETE'
            ], 500);
        }
    }

    /**
     * Logout all devices.
     */
    public function logoutAllDevices(Request $request)
    {
        try {
            $allTokens = $request->user()->tokens();
            $allTokens->delete();
            ApcisToken::where('user_id', $request->user()->id)->delete();

            return response()->json([
                'status' => true,
                'message' => 'Logged out from all devices',
                'method' => 'DELETE'
            ], 200);
        } catch (\Exception) {
            return response()->json([
                'status' => false,
                'error' => 'Unexpected logout error',
                'method' => 'DELETE'
            ], 500);
        }
    }
}
