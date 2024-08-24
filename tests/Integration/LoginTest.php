<?php
namespace Tests\Integration;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Services\AuthService;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_fails_when_apcis_api_login_request_fails()
    {
        // Mock the HTTP client to throw a RequestException
        Http::fake([
            '*' => Http::response('', 500) // Simulate a 500 server error response
        ]);

        // Create a mock request with valid login data
        $loginData = [
            'email' => 'jbberbon@student.apc.edu.ph',
            'password' => '12345678',
            'remember_me' => true
        ];

        // Send a POST request to the login endpoint
        $response = $this->postJson('/api/login', $loginData);

        // Assert that the response status code is 500
        $response->assertStatus(500);

        // Assert that the response contains the correct error message
        $response->assertJson([
            'status' => false,
            'error' => 'APCIS API login request failed',
            'method' => 'POST'
        ]);
    }
}
