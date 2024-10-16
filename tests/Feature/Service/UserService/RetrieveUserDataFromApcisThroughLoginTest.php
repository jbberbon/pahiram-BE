<?php
namespace Tests\Feature\Service\UserService;

use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RetrieveUserDataFromApcisThroughLoginTest extends TestCase
{
    private $baseURL;
    private $userService;

    private $loginCredentials;

    protected function setUp(): void
    {
        parent::setUp(); // Call the parent setUp method
        $this->baseURL = env('APCIS_URL') . '/*'; // Initialize the baseURL property

        $this->userService = new UserService();

        $this->loginCredentials = [
            'email' => 'sample@email.com',
            'password' => 'password',
            'remember_me' => true
        ];
    }

    /**
     * 01. Able to retrieve user successfully
     * 02. Not able to retrieve user with wrong credentials
     * 03. Not able to retrieve user with down server
     */

    // 01. Able to retrieve user successfully
    public function test_able_to_retrieve_user_successfully()
    {
        // Fake the API response for a successful request
        Http::fake([
            $this->baseURL => Http::response([
                'status' => true,
                'data' => [
                    'user' => [
                        'apc_id' => 'XXX-XXX',
                        'first_name' => 'John Doe',
                        'last_name' => 'Ok',
                        'email' => 'jdok@apc.edu.ph',
                    ],
                    'apcis_token' => [
                        'access_token' => 'valid-token',
                        'expires_at' => 'valid-date'
                    ]
                ],
                'method' => 'POST'
            ], 200),
        ]);

        // Call the method
        $response = $this->userService
            ->retrieveUserDataFromApcisThroughLogin($this->loginCredentials);

        // Assert the structure of the response
        $this->assertEquals([
            'status' => true,
            'data' => [
                'user' => [
                    'apc_id' => 'XXX-XXX',
                    'first_name' => 'John Doe',
                    'last_name' => 'Ok',
                    'email' => 'jdok@apc.edu.ph',
                ],
                'apcis_token' => [
                    'access_token' => 'valid-token',
                    'expires_at' => 'valid-date'
                ]
            ],
            'method' => 'POST'
        ], $response);
    }

    // 02. Not able to retrieve user with wrong credentials
    public function test_not_able_to_retrieve_user_with_wrong_credentials()
    {
        // Fake the API response for a successful request
        Http::fake([
            $this->baseURL => Http::response([
                'status' => false,
                'message' => 'Wrong credentials',
                'method' => 'POST'
            ], 401),
        ]);

        // Call the method
        $response = $this->userService
            ->retrieveUserDataFromApcisThroughLogin($this->loginCredentials);
        \Log::info("TEST", ['controller' => $response]);

        // Assert the structure of the response
        $this->assertEquals(response()->json([
            'status' => false,
            'message' => 'Wrong credentials',
            'method' => 'POST'
        ], 401), $response);
    }
}