<?php
namespace Tests\Feature\Service\UserService;

use App\Services\UserService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RetrieveUserDataFromApcisWithoutLoginTest extends TestCase
{
    private $baseURL;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed the database before each test
        $this->seed();

        $this->baseURL = env('APCIS_URL') . '/*';
    }
    /**
     * Test a successful API response.
     *
     * @return void
     */
    public function testRetrieveUserDataSuccessfully()
    {
        $apcId = '12345';
        $apcisToken = 'validToken';

        // Fake the API response for a successful request
        Http::fake([
            $this->baseURL => Http::response([
                'status' => true,
                'data' => [
                    'apc_id' => $apcId,
                    'first_name' => 'John Doe',
                    'last_name' => 'Ok',
                    'email' => 'jdok@apc.edu.ph',
                ],
            ], 200),
        ]);

        // Call the method
        $response = $this->app->make(UserService::class)
            ->retrieveUserDataFromApcisWithoutLogin($apcId, $apcisToken);

        // Assert the structure of the response
        $this->assertArrayHasKey('apc_id', $response);
        $this->assertArrayHasKey('first_name', $response);
        $this->assertArrayHasKey('last_name', $response);
        $this->assertArrayHasKey('email', $response);
    }

    /**
     * Test an API request that fails with a bad token.
     *
     * @return void
     */
    public function testRetrieveUserDataWithInvalidToken()
    {
        $apcId = '12345';
        $apcisToken = 'invalidToken';

        Http::fake([
            $this->baseURL => Http::response([
                "message" => 'Unauthenticated.'
            ], 401),
        ]);

        // Call the method
        $response = $this->app->make(UserService::class)->retrieveUserDataFromApcisWithoutLogin($apcId, $apcisToken);

        // Assert the error response
        $this->assertIsArray($response);
        $this->assertArrayHasKey('status', $response);
        $this->assertArrayHasKey('error', $response);
        $this->assertArrayHasKey('method', $response);

        $this->assertEquals(false, $response['status']);
        $this->assertEquals("Something went wrong. Try again later.", $response['error']);

    }

    /**
     * Test when the API responds with status false.
     *
     * @return void
     */
    public function testRetrieveUserDataWithApiStatusFalse()
    {
        $apcId = '12345';
        $apcisToken = 'validToken';

        Http::fake([
            $this->baseURL => Http::response([
                'status' => false,
                'message' => 'User not found',
                'method' => 'GET',
            ], 404),
        ]);

        // Call the method
        $response = $this->app->make(UserService::class)->retrieveUserDataFromApcisWithoutLogin($apcId, $apcisToken);

        // Assert the error response
        $this->assertIsArray($response);
        $this->assertArrayHasKey('status', $response);
        $this->assertArrayHasKey('error', $response);
        $this->assertArrayHasKey('method', $response);

        $this->assertEquals(false, $response['status']);
        $this->assertEquals("Something went wrong. Try again later.", $response['error']);

    }
}
