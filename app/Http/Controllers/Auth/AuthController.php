<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\ApcisToken;
use App\Models\Course;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Utils\NewUserDefaultData;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    /**
     * Login Method
     */
    public function login(LoginRequest $request)
    {
        $validatedData = $request->validated();
        try {
            /**
             * 2. Access APCIS login API
             */
            $response = Http::timeout(20)->post('http://167.172.74.157/api/login', $validatedData);
            $apiReturnData = json_decode($response->body(), true);

            // login API returns false
            if ($apiReturnData['status'] == false) {
                return response($apiReturnData, 401);
            }

            $apiUserData = $apiReturnData['data']['user'];
            $apiCourseData = $apiReturnData['data']['course'];
            $apiTokenData = $apiReturnData['data']['apcis_token'];

            /**
             * 3. Check COURSE if already exist in pahiram-BE Database
             */
            $course = Course::where('course', $apiCourseData['course'])->first();
            // Does not exist yet, add to db, else do nothing
            if (!$course) {
                $course = Course::create($apiCourseData);
            }

            /**
             * 4. Check USER if already exist in pahiram-BE Database
             */
            $user = User::where('email', $apiUserData['email'])->firstOrFail();
            // Does not exist yet, add user to db
            if (!$user) {
                $defaultData = NewUserDefaultData::defaultData($course);
                $newUser = array_merge($apiUserData, $defaultData);
                $user = User::create($newUser);
            }

            /**
             * 5. Generate Pahiram Token with expiration
             */
            $expiresAt = \DateTime::createFromFormat('Y-m-d H:i:s', $apiTokenData['expires_at']);
            $pahiramToken = $user->createToken('Pahiram-Token', ['*'], $expiresAt)->plainTextToken;

            /**
             * 6. Store APCIS token to Pahiram DB
             */
            $newToken = [
                'user_id' => $user->id,
                'token' => $apiTokenData['access_token'],
                'expires_at' => $expiresAt
            ];
            $apcisToken = ApcisToken::create($newToken);

            
            // Success return values 
            // make dept_id as code, role also,
            $roleCode = Role::where('id', $user->user_role_id)->firstOrFail()->role_code;

            $departmentCode = null;
            if ($user->department_id !== null) {
                $departmentCode = Department::where('id', $user->department_id)->firstOrFail()->department_code;
            }

            unset($user['department_id']);
            unset($user['user_role_id']);
            return response([
                'status' => true,
                'data' => [
                    'user' => [
                        ...$user->toArray(),
                        'department_code' => $departmentCode,
                        'role_code' => $roleCode
                    ],
                    'pahiram_token' => $pahiramToken,
                    'apcis_token' => $apcisToken['token']
                ],
                'method' => 'POST'
            ], 200);

        } catch (RequestException $exception) {
            // Handle HTTP request exception
            \Log::error('API Request Failed:', ['exception' => $exception->getMessage()]);

            return response([
                'status' => false,
                'error' => 'APCIS API login request failed',
                'method' => 'POST'
            ], 500);
        } catch (\Exception $exception) {
            // Handle other exceptions
            \Log::error('Unexpected Exception:', ['exception' => $exception->getMessage()]);
            return response([
                'status' => false,
                'error' => 'Unexpected error',
                'method' => 'POST'
            ], 500);
        }

    }

    /**
     * Logout current session.
     */
    public function logout(User $user)
    {
        $user->currentAccessToken()->delete();

        return response([
            'status' => true,
            'message' => 'Logged out',
            'method' => 'DELETE'
        ], 200);
    }

    /**
     * Logout all devices.
     */
    public function logoutAllDevices(User $user)
    {
        $user->tokens()->delete();

        return response([
            'status' => true,
            'message' => 'Logged out for all devices',
            'method' => 'DELETE'
        ], 200);
    }
}
