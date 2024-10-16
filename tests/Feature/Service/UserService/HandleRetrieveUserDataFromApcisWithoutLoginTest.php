<?php
namespace Tests\Feature\Service\UserService;

use App\Models\AccountStatus;
use App\Models\Role;
use App\Models\User;
use App\Services\UserService;
use App\Utils\Constants\Statuses\ACCOUNT_STATUS;
use App\Utils\Constants\USER_ROLE;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HandleRetrieveUserDataFromApcisWithoutLoginTest extends TestCase
{
    use RefreshDatabase;
    private $baseURL;
    protected function setUp(): void
    {
        parent::setUp(); // Call the parent setUp method
        $this->baseURL = env('APCIS_URL') . '/*'; // Initialize the baseURL property

        $this->seed();
    }

    /*
     * 01. User exists in apcis and able to store into db
     * 02. User does not exist on apcis
     * 03. Api error
     */

    // 01. User exists in apcis and able to store into db
    public function test_user_exists_in_apcis_and_able_to_store_into_db()
    {
        $apcId = '12345';
        $apcisToken = 'validToken';
        $borrowerRoleId = Role::getIdByRole(USER_ROLE::BORROWER);
        $activeAccStatusId = AccountStatus::getIdByStatus(ACCOUNT_STATUS::ACTIVE);

        // Fake the API response for a successful request
        Http::fake([
            $this->baseURL => Http::response([
                'status' => true,
                'data' => [
                    'apc_id' => "XXXX",
                    'first_name' => "Juan",
                    'last_name' => "de la Cruz",
                    'email' => "jdcruz@apc.edu.ph"
                ],
                'method' => "GET"
            ], 200),
        ]);

        // Call the method
        $methodResponse = $this->app->make(UserService::class)
            ->handleRetrieveUserDataFromApcisWithoutLogin($apcId, $apcisToken);
        // Get the user
        $user = User::first();

        // Assert method return value
        $this->assertEquals(null, $methodResponse);

        // Assert Stored User
        $this->assertEquals("XXXX", $user->apc_id);
        $this->assertEquals("Juan", $user->first_name);
        $this->assertEquals("de la Cruz", $user->last_name);
        $this->assertEquals("jdcruz@apc.edu.ph", $user->email);

        // Assert that user role and status are correctly set
        $this->assertEquals($borrowerRoleId, $user->user_role_id);
        $this->assertEquals($activeAccStatusId, $user->acc_status_id);
    }

    // 02. User does not exist on apcis
    public function test_user_does_not_exist_on_apcis()
    {
        $apcId = '12345';
        $apcisToken = 'validToken';

        // Fake the API response for a successful request
        Http::fake([
            $this->baseURL => Http::response([
                'status' => false,
                'message' => '',
                'method' => "GET"
            ], 200),
        ]);

        // Call the method
        $methodResponse = $this->app->make(UserService::class)
            ->handleRetrieveUserDataFromApcisWithoutLogin($apcId, $apcisToken);

        // Assert method return value
        $this->assertIsArray($methodResponse);
        $this->assertArrayHasKey('error', $methodResponse);
        $this->assertEquals("Something went wrong. Try again later.", $methodResponse['error']);
    }


    // 03. Api 500 error
    public function test_api_500_error()
    {
        $apcId = '12345';
        $apcisToken = 'validToken';

        // Fake the API response for a successful request
        Http::fake([
            $this->baseURL => Http::response([], 500),
        ]);

        // Call the method
        $methodResponse = $this->app->make(UserService::class)
            ->handleRetrieveUserDataFromApcisWithoutLogin($apcId, $apcisToken);

        // Assert method return value
        $this->assertIsArray($methodResponse);
        $this->assertArrayHasKey('error', $methodResponse);
        $this->assertEquals("Something went wrong. Try again later.", $methodResponse['error']);
    }

    // 04. Api 404 error
    public function test_api_404_error()
    {
        $apcId = '12345';
        $apcisToken = 'validToken';

        // Fake the API response for a successful request
        Http::fake([
            $this->baseURL => Http::response([], 404),
        ]);

        // Call the method
        $methodResponse = $this->app->make(UserService::class)
            ->handleRetrieveUserDataFromApcisWithoutLogin($apcId, $apcisToken);

        // Assert method return value
        $this->assertIsArray($methodResponse);
        $this->assertArrayHasKey('error', $methodResponse);
        $this->assertEquals("Something went wrong. Try again later.", $methodResponse['error']);
    }
}