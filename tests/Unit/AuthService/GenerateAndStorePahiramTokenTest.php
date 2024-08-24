<?php

namespace Tests\Unit;

use App\Services\AuthService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PHPUnit\Framework\TestCase;

class GenerateAndStorePahiramTokenTest extends TestCase
{
    use RefreshDatabase;

    public function testGenerateAndStorePahiramTokenSuccess()
    {
        // Arrange
        $authService = new AuthService();
        $user = Mockery::mock(User::class);
        $expiresAt = '2024-12-31 23:59:59';

        // Mock createToken method
        $user->shouldReceive('createToken')
            ->once()
            ->with('Pahiram-Token', ['*'], \DateTime::createFromFormat('Y-m-d H:i:s', $expiresAt))
            ->andReturn((object) ['plainTextToken' => 'sample-pahiram-token']);

        // Act
        $result = $authService->generateAndStorePahiramToken($user, $expiresAt);

        // Assert
        $this->assertIsString($result);
        $this->assertEquals('sample-pahiram-token', $result);
    }



    public function testGenerateAndStorePahiramTokenFailure()
    {
        // Arrange
        $authService = new AuthService();
        $user = Mockery::mock(User::class);
        $expiresAt = '2024-12-31 23:59:59';

        // Mock createToken method to throw exception
        $user->shouldReceive('createToken')
            ->once()
            ->with('Pahiram-Token', ['*'], \DateTime::createFromFormat('Y-m-d H:i:s', $expiresAt))
            ->andThrow(new \Exception('Error generating token'));

        // Act
        $result = $authService->generateAndStorePahiramToken($user, $expiresAt);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('error', $result);
        $this->assertFalse($result['status']);
        $this->assertEquals('Something went wrong with generating Pahiram Token', $result['error']);
    }



    // Clean up Mockery
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
