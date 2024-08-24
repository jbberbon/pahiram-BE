<?php

namespace Tests\Feature;

use Http;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();

        // Seed the database before each test
        $this->seed(); // Or specify a specific seeder class
    }

    public function test_login_success(): void
    {
        $this->withoutExceptionHandling();
        // Preparation
        $credentials = [
            'email' => 'jbberbon@student.apc.edu.ph',
            'password' => '12345678',
            'remember_me' => false
        ];

        // Action
        $response = $this->postJson('api/login', $credentials);


        // Assertion
        $response->assertStatus(200);

        // Assert that the response has the main keys
        $response->assertJsonStructure([
            'status',
            'data' => [
                'user' => [
                    'apc_id',
                    'first_name',
                    'last_name',
                    'email',
                    'role',
                    'acc_status',
                    'department'
                ],
                'pahiram_token',
                'apcis_token',
                'expires_at'
            ],
            'method'
        ]);
    }

    public function test_login_wrong_email_but_apc_domain(): void
    {
        $this->withoutExceptionHandling();
        // Preparation
        $credentials = [
            'email' => 'wrongEmail@student.apc.edu.ph',
            'password' => '12345678',
            'remember_me' => false
        ];

        // Action
        $response = $this->postJson('api/login', $credentials);


        // Assertion
        $response->assertStatus(401);
        $response->assertJsonStructure([
            'status',
            'message',
            'method'
        ]);
    }

    public function test_login_not_apc_email(): void
    {
        $this->withoutExceptionHandling();
        // Preparation
        $credentials = [
            'email' => 'wrongEmail@gmail.com',
            'password' => '12345678',
            'remember_me' => false
        ];

        // Action
        $response = $this->postJson('api/login', $credentials);


        // Assertion
        $response->assertStatus(422);
        $response->assertJsonStructure([
            'status',
            'message',
            'errors' => ['email'],
            'method'
        ]);
    }

    public function test_login_wrong_password_but_apc_domain(): void
    {
        $this->withoutExceptionHandling();
        // Preparation
        $credentials = [
            'email' => 'jbberbon@student.apc.edu.ph',
            'password' => '1234567',
            'remember_me' => false
        ];

        // Action
        $response = $this->postJson('api/login', $credentials);


        // Assertion
        $response->assertStatus(401);
        $response->assertJsonStructure([
            'status',
            'message',
            'method'
        ]);
    }

    public function test_down_apcis_server(): void
    {
        // Simulate a down API server by returning a 500 status code
        Http::fake([
            env('APCIS_URL') . '/login' => Http::response([], 500),
        ]);

        // Prepare the request data
        $credentials = [
            'email' => 'test@student.apc.edu.ph',
            'password' => '12345678',
            'remember_me' => true,
        ];

        // Action
        $response = $this->postJson('api/login', $credentials);

        // Assertion
        $response->assertStatus(500);
        $response->assertJson([
            'status' => false,
            'error' => 'Unexpected auth server response',
            'method' => 'POST',
        ]);
    }

    public function test_unexpected_response_format_apcis_server(): void
    {
        // Simulate an unexpected response format from the external API
        Http::fake([
            env('APCIS_URL') . '/login' => Http::response([
                // Simulate an unexpected structure or missing data
                'unexpected_field' => 'value'
            ], 200),
        ]);

        // Prepare the request data
        $credentials = [
            'email' => 'test@student.apc.edu.ph',
            'password' => '12345678',
            'remember_me' => true,
        ];

        // Action
        $response = $this->postJson('api/login', $credentials);

        // Assertion
        $response->assertStatus(200);
        $response->assertJson([
            'status' => false,
            'error' => 'Unexpected auth server response',
            'method' => 'POST',
        ]);
    }

    public function test_user_data_does_not_match_schema(): void
    {
        // Simulate the APCIS API response with incorrect user data
        Http::fake([
            env('APCIS_URL') . '/login' => Http::response([
                'status' => true,
                'data' => [
                    'user' => [
                        'apc_id' => '2021-140966',
                        'first_name' => null, // Simulate null data
                        'last_name' => 'Berbon',
                        'email' => 'jbberbon@student.apc.edu.ph'
                    ],
                    'apcis_token' => [
                        'access_token' => 'SFSFSFSF',
                        'expires_at' => '2024-08-31 16:10:57'
                    ]
                ],
                'method' => 'POST'
            ], 200),
        ]);

        // Prepare the request data
        $credentials = [
            'email' => 'test@student.apc.edu.ph',
            'password' => '12345678',
            'remember_me' => true,
        ];

        // Action
        $response = $this->postJson('api/login', $credentials);

        // Assertion: check if the response status is 500 due to schema mismatch
        $response->assertStatus(200);
        $response->assertJson([
            'status' => false,
            'error' => 'Unexpected auth server response',
            'method' => 'POST',
        ]);
    }

}
