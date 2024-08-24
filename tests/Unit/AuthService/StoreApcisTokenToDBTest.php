<?php

namespace Tests\Unit\AuthService;

use App\Models\ApcisToken;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class StoreApcisTokenToDBTest extends TestCase
{
    use RefreshDatabase;

    public function testStoreApcisTokenToDBSuccess()
    {
        // Arrange
        $authService = new AuthService();
        $userID = '12345';
        $apcisTokenData = [
            'access_token' => 'sample-access-token',
            'expires_at' => '2024-12-31 23:59:59',
        ];

        // Mock ApcisToken model's create method
        $mockApcisToken = Mockery::mock(ApcisToken::class);
        $mockApcisToken->shouldReceive('create')
            ->with([
                'user_id' => $userID,
                'token' => $apcisTokenData['access_token'],
                'expires_at' => $apcisTokenData['expires_at'],
            ])
            ->andReturn(true);

        // Bind the mock to the container
        $this->app->instance(ApcisToken::class, $mockApcisToken);

        // Act
        $result = $authService->storeApcisTokenToDB($userID, $apcisTokenData);

        // Assert
        $this->assertNull($result);
    }

    public function testStoreApcisTokenToDBFailure()
    {
        // Arrange
        $authService = new AuthService();
        $userID = '12345';
        $apcisTokenData = [
            'access_token' => 'sample-access-token',
            'expires_at' => '2024-12-31 23:59:59',
        ];

        // Mock ApcisToken model's create method to throw an exception
        $mockApcisToken = Mockery::mock(ApcisToken::class);
        $mockApcisToken->shouldReceive('create')
            ->andThrow(new \Exception('Database error'));

        // Bind the mock to the container
        $this->app->instance(ApcisToken::class, $mockApcisToken);

        // Act
        $result = $authService->storeApcisTokenToDB($userID, $apcisTokenData);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('error', $result);
        $this->assertFalse($result['status']);
        $this->assertEquals('Something went wrong with storing APCIS Token', $result['error']);
    }

    // Clean up Mockery
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
