<?php

namespace Tests\Unit;

use App\Services\AuthService;
use App\Models\User;
use App\Models\Role;
use App\Models\AccountStatus;
use App\Models\UserDepartment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class RetrieveUserLoginDataTest extends TestCase
{
    use RefreshDatabase;

    public function testRetrieveUserLoginDataSuccess()
    {
        // Arrange
        $authService = new AuthService();
        $user = Mockery::mock(User::class);
        $user->apc_id = '12345';
        $user->first_name = 'John';
        $user->last_name = 'Doe';
        $user->email = 'jbberbon@student.apc.edu.ph';
        $user->user_role_id = 1;
        $user->acc_status_id = 1;
        $user->id = 1;

        // Mock static methods
        Mockery::mock('alias:' . Role::class)
            ->shouldReceive('getRoleById')
            ->with($user->user_role_id)
            ->andReturn('Admin');

        Mockery::mock('alias:' . AccountStatus::class)
            ->shouldReceive('getStatusById')
            ->with($user->acc_status_id)
            ->andReturn('Active');

        Mockery::mock('alias:' . UserDepartment::class)
            ->shouldReceive('getDepartmentAcronymByUserId')
            ->with($user->id)
            ->andReturn('HR');

        // Act
        $result = $authService->retrieveUserLoginData(
            $user,
            'sample-pahiram-token',
            'sample-apcis-token',
            '2024-12-31T23:59:59Z'
        );

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('pahiram_token', $result);
        $this->assertArrayHasKey('apcis_token', $result);
        $this->assertArrayHasKey('expires_at', $result);

        $this->assertEquals([
            'apc_id' => '12345',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'jbberbon@student.apc.edu.ph',
            'role' => 'Admin',
            'acc_status' => 'Active',
            'department' => 'HR'
        ], $result['user']);

        $this->assertEquals('sample-pahiram-token', $result['pahiram_token']);
        $this->assertEquals('sample-apcis-token', $result['apcis_token']);
        $this->assertEquals('2024-12-31T23:59:59Z', $result['expires_at']);
    }

    public function testRetrieveUserLoginDataFailure()
    {
        // Arrange
        $authService = new AuthService();
        $user = Mockery::mock(User::class);

        // Simulate exception when accessing user properties or static methods
        $user->shouldReceive('apc_id')->andThrow(new \Exception('Error accessing property'));
        $user->shouldReceive('first_name')->andThrow(new \Exception('Error accessing property'));

        // Act
        $result = $authService->retrieveUserLoginData(
            $user,
            'sample-pahiram-token',
            'sample-apcis-token',
            '2024-12-31T23:59:59Z'
        );

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('error', $result);
        $this->assertFalse($result['status']);
        $this->assertEquals("Something went wrong with retrieving user data", $result['error']);
    }

    // Clean up Mockery
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
