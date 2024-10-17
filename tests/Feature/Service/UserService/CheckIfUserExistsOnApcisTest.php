<?php
namespace Tests\Feature\Service\UserService;

use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckIfUserExistsOnApcisTest extends TestCase
{
    private $baseURL;
    protected function setUp(): void
    {
        parent::setUp(); // Call the parent setUp method
        $this->baseURL = env('APCIS_URL'); // Initialize the baseURL property
    }

    /**
     * Test a successful API response.
     *
     * @return void
     */
    public function test_user_does_exist_on_apcis()
    {
        $apcId = '12345';
        $apcisToken = 'validToken';

        // Fake the API response for a successful request
        Http::fake([
            $this->baseURL . '/*' => Http::response([
                'status' => true,
                'data' => true,
                'method' => "GET"
            ], 200),
        ]);

        // Call the method
        $response = $this->app->make(UserService::class)
            ->checkIfUserExistsOnApcis($apcId, $apcisToken);

        $this->assertEquals(true, $response);
    }


    /**
     * Test a not existing resource
     *
     * @return void
     */
    public function test_user_does_not_exist_on_apcis()
    {
        $apcId = '12345';
        $apcisToken = 'validToken';

        // Fake the API response for a successful request
        Http::fake([
            $this->baseURL . '/*' => Http::response([
                'status' => true,
                'data' => false,
                'method' => "GET"
            ], 200),
        ]);

        // Call the method
        $response = $this->app->make(UserService::class)
            ->checkIfUserExistsOnApcis($apcId, $apcisToken);

        $this->assertEquals(false, $response);
    }

    /**
     * Test a not existing resource
     *
     * @return void
     */
    public function test_failed_api_request()
    {
        $apcId = '12345';
        $apcisToken = 'validToken';

        // Fake the API response for a successful request
        Http::fake([
            $this->baseURL . '/*' => Http::response([], 500),
        ]);

        // Call the method
        $response = $this->app->make(UserService::class)
            ->checkIfUserExistsOnApcis($apcId, $apcisToken);

        $this->assertEquals(false, $response);
    }
}