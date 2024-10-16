<?php

namespace Tests\Feature;

use Http;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;
    private $baseURL;
    protected function setUp(): void
    {
        parent::setUp();

        // Seed the database before each test
        $this->seed();
        $this->baseURL = env('APCIS_URL'); // Initialize the baseURL property
    }

    public function test_login_success(): void
    {
        $this->withoutExceptionHandling();
        // Preparation
        Http::fake([
            $this->baseURL . '/*' => Http::response([
                'status' => true,
                'data' => [
                    'user' => [
                        'apc_id' => 'XXX-XXX',
                        'first_name' => 'John Doe',
                        'last_name' => 'Ok',
                        'email' => 'jdok@apc.edu.ph',
                    ],
                    'apcis_token' => [
                        'access_token' => '29|qXK2ITbwIsc7O16vta3GzA8YRQhjjRSn1Nqyi86H439de82b',
                        'expires_at' => "2024-10-22 17:37:38"
                    ]
                ],
                'method' => 'POST'
            ], 200),
        ]);

        $credentials = [
            'email' => 'jdok@apc.edu.ph',
            'password' => '12345678',
            'remember_me' => false
        ];

        // Action
        $response = $this->postJson('api/login', $credentials);

        // Assert that the response is 200
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([
            'apc_id' => 'XXX-XXX',
            'first_name' => 'John Doe',
            'last_name' => 'Ok',
            'email' => 'jdok@apc.edu.ph',
            "role" => "BORROWER",
            "acc_status" => "ACTIVE",
            "department" => null,
        ], $response->json('data.user'));
    }


    public function test_login_wrong_email_but_apc_domain(): void
    {
        $this->withoutExceptionHandling();
        // Preparation
        Http::fake([
            $this->baseURL . '/*' => Http::response([
                'status' => false,
                'message' => 'Wrong credentials',
                'method' => 'POST'
            ], 401),
        ]);

        $credentials = [
            'email' => 'wrongEMAIL@apc.edu.ph',
            'password' => '12345678',
            'remember_me' => false
        ];

        // Action
        $response = $this->postJson('api/login', $credentials);

        // Assert that the response is 200
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals([
            'status' => false,
            'message' => 'Wrong credentials',
            'method' => 'POST'
        ], $response->json());
    }

    public function test_login_not_apc_email(): void
    {
        $this->withoutExceptionHandling();
        // Preparation
        Http::fake([
            $this->baseURL . '/*' => Http::response([
                'status' => false,
                'message' => 'Login Failed',
                'errors' => [
                    'email' => 'The email field format is invalid.'
                ],
                'method' => 'POST'
            ], 422),
        ]);

        $credentials = [
            'email' => 'wrongEmail@gmail.com',
            'password' => '12345678',
            'remember_me' => false
        ];

        // Action
        $response = $this->postJson('api/login', $credentials);

        // Assertion
        $response->assertStatus(422);
        $this->assertEquals([
            'status' => false,
            'message' => 'Login Failed',
            'errors' => [
                'email' => 'The email field format is invalid.'
            ],
            'method' => 'POST'
        ], $response->json());
    }

    public function test_down_apcis_server(): void
    {
        // Simulate a down API server by returning a 500 status code
        Http::fake([
            env('APCIS_URL') . '/login' => Http::response([], 503),
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
        $response->assertStatus(503);
        $response->assertJson([
            'status' => false,
            'error' => 'Unexpected auth server response.',
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
         $response->assertStatus(502);
         $response->assertJson([
             'status' => false,
             'error' => 'Unexpected auth server response.',
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
         $response->assertStatus(502);
         $response->assertJson([
             'status' => false,
             'error' => 'Unexpected auth server response.',
             'method' => 'POST',
         ]);
     }

}
