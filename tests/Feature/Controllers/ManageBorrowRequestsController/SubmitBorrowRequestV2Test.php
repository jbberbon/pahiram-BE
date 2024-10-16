<?php

namespace Tests\Feature\Controllers\ManageBorrowingRequestController;

use App\Http\Controllers\BorrowTransaction\ManageBorrowingRequestController;
use App\Models\AccountStatus;
use App\Models\BorrowedItem;
use App\Models\BorrowTransaction;
use App\Models\Department;
use App\Models\ItemGroup;
use App\Models\Role;
use App\Models\User;
use App\Utils\Constants\Statuses\ACCOUNT_STATUS;
use App\Utils\Constants\USER_ROLE;
use Http;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class SubmitBorrowRequestV2Test extends TestCase
{
    use RefreshDatabase;

    protected $itroId;
    protected $esloId;
    protected $bmoId;
    protected $itemGroups;
    protected $controller;
    protected $requestedItemsForThreeOffices;
    protected $apcisUrl;

    // Default New User Data
    protected $borrowerRoleId;
    protected $activeAccStatusId;

    private function getSuccessResponse(int $transacCount)
    {
        return [
            "status" => true,
            "message" => "Successfully submitted " . $transacCount . " borrow request",
            "method" => "POST"
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->seed();

        $this->apcisUrl = env('APCIS_URL'); // Initialize the baseURL property
        $this->borrowerRoleId = Role::getIdByRole(USER_ROLE::BORROWER);
        $this->activeAccStatusId = AccountStatus::getIdByStatus(ACCOUNT_STATUS::ACTIVE);

        // Create a user
        $user = User::factory()->create();
        // Log the user in
        $this->actingAs($user);

        $this->controller = app(ManageBorrowingRequestController::class);
        $this->itroId = Department::getIdBasedOnAcronym('ITRO');
        $this->esloId = Department::getIdBasedOnAcronym('ESLO');
        $this->bmoId = Department::getIdBasedOnAcronym('BMO');


        // Create necessary item groups
        $this->itemGroups = [
            'Canon 200d' => ItemGroup::getIdByModelNameAndOfficeId('Canon 200d', $this->itroId),
            'Arduino Uno R4 WiFi' => ItemGroup::getIdByModelNameAndOfficeId('Arduino Uno R4 WiFi', $this->itroId),
            'FLUKE T150 Voltage Tester' => ItemGroup::getIdByModelNameAndOfficeId('FLUKE T150 Voltage Tester', $this->esloId),
            'Micron Cresta ZS Microscope' => ItemGroup::getIdByModelNameAndOfficeId('Micron Cresta ZS Microscope', $this->esloId),
            'Lifetime 6ft Folding Table' => ItemGroup::getIdByModelNameAndOfficeId('Lifetime 6ft Folding Table', $this->bmoId),
            'Spalding FIBA 2007' => ItemGroup::getIdByModelNameAndOfficeId('Spalding FIBA 2007', $this->bmoId),
        ];

        $this->requestedItemsForThreeOffices = [
            // ITRO
            [
                'item_group_id' => $this->itemGroups['Canon 200d'],
                'quantity' => 3,
                'start_date' => (string) now()->addDay(),
                'return_date' => (string) now()->addWeek(),
            ],
            [
                'item_group_id' => $this->itemGroups['Arduino Uno R4 WiFi'],
                'quantity' => 3,
                'start_date' => (string) now()->addDay(),
                'return_date' => (string) now()->addWeek(),
            ],

            // ESLO
            [
                'item_group_id' => $this->itemGroups['FLUKE T150 Voltage Tester'],
                'quantity' => 3,
                'start_date' => (string) now()->addDay(),
                'return_date' => (string) now()->addWeek(),
            ],
            [
                'item_group_id' => $this->itemGroups['Micron Cresta ZS Microscope'],
                'quantity' => 3,
                'start_date' => (string) now()->addDay(),
                'return_date' => (string) now()->addWeek(),
            ],

            // BMO
            [
                'item_group_id' => $this->itemGroups['Lifetime 6ft Folding Table'],
                'quantity' => 3,
                'start_date' => (string) now()->addDay(),
                'return_date' => (string) now()->addWeek(),
            ],
            [
                'item_group_id' => $this->itemGroups['Spalding FIBA 2007'],
                'quantity' => 3,
                'start_date' => (string) now()->addDay(),
                'return_date' => (string) now()->addWeek(),
            ],
        ];
    }
    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /*
     * 01. Able to submit request for 1 office without endorser
     * 02. Able to submit request for 3 offices without endorser
     * 
     * 03. Able to submit request for 3 offices with endorser existing on pahiram db
     * 04. Able to submit request for 3 offices with endorser not yet existing on pahiram db
     * 05. Two duplicate item group id results to failed validation
     */

    // 01. Able to submit request for 1 office without endorser
    public function test_able_to_submit_request_for_one_office_wo_endorser()
    {
        $this->withoutMiddleware();
        $requestData = [
            'purpose' => "OTHERS",
            'user_defined_purpose' => 'sfsfs',
            "items" => [
                // ITRO
                $this->requestedItemsForThreeOffices[0],
                $this->requestedItemsForThreeOffices[1],
            ]
        ];

        $response = $this->postJson('api/user/borrow-request/submit-V2', $requestData);
        $borrowedItems = BorrowedItem::count();
        $transactionCount = BorrowTransaction::count();

        // Assert Controller Return Data
        $response->assertJsonStructure([
            'status',
            'message',
            'method'
        ]);
        $response->assertJson(self::getSuccessResponse(1));

        // Assert Transaction Values
        $this->assertEquals($borrowedItems, 6);
        $this->assertEquals($transactionCount, 1);
    }


    // 02. Able to submit request for 3 offices without endorser
    public function test_able_to_submit_request_for_three_offices_wo_endorser()
    {
        $this->withoutMiddleware(); // Skips all middleware

        $requestData = [
            'purpose' => "OTHERS",
            'user_defined_purpose' => 'sfsfs',
            "items" => $this->requestedItemsForThreeOffices
        ];

        $response = $this->postJson('api/user/borrow-request/submit-V2', $requestData);
        $borrowedItems = BorrowedItem::count();
        $transactionCount = BorrowTransaction::count();

        // Assert Controller Return Data
        $response->assertJsonStructure([
            'status',
            'message',
            'method'
        ]);
        $response->assertJson(self::getSuccessResponse(3));

        // Assert Transaction Values
        $this->assertEquals($borrowedItems, 18);
        $this->assertEquals($transactionCount, 3);
    }

    // 03. Able to submit request for 3 offices with endorser existing on pahiram db
    public function test_able_to_submit_request_for_three_offices_w_endorser_existing_on_pahiram_db()
    {
        $this->withoutMiddleware(); // Skips all middleware

        $requestData = [
            'endorsed_by' => 'XXX-XXX',
            'apcis_token' => '1306|WeLWJb5xAdtAhZ5FUSIz5MgIzPgiSvG84botQ8yv8070c589',
            'purpose' => "OTHERS",
            'user_defined_purpose' => 'sfsfs',
            "items" => $this->requestedItemsForThreeOffices
        ];

        $endorserResource = User::create([
            'apc_id' => 'XXX-XXX',
            'first_name' => 'Juan',
            'last_name' => "de la Cruz",
            'email' => "jdcruz@apc.edu.ph",
            'user_role_id' => $this->borrowerRoleId,
            'acc_status_id' => $this->activeAccStatusId
        ]);

        $response = $this->postJson('api/user/borrow-request/submit-V2', $requestData);
        $borrowedItems = BorrowedItem::count();
        $transaction = BorrowTransaction::all();

        // Assert Controller Return Data
        $response->assertJsonStructure([
            'status',
            'message',
            'method'
        ]);
        $response->assertJson(self::getSuccessResponse(3));

        // Assert Transaction Values
        $this->assertEquals($transaction->first()->endorsed_by, $endorserResource->id);
        $this->assertEquals($borrowedItems, 18);
        $this->assertEquals($transaction->count(), 3);
    }



    // 04. Able to submit request for 3 offices with endorser not yet existing on pahiram db
    public function test_able_to_submit_request_for_three_offices_w_endorser_not_yet_existing_on_pahiram_db()
    {
        $this->withoutMiddleware(); // Skips all middleware
        $requestData = [
            'endorsed_by' => 'XXX-XXX',
            'apcis_token' => '1306|WeLWJb5xAdtAhZ5FUSIz5MgIzPgiSvG84botQ8yv8070c589',
            'purpose' => "OTHERS",
            'user_defined_purpose' => 'sfsfs',
            "items" => $this->requestedItemsForThreeOffices
        ];

        Http::fake([
            $this->apcisUrl . '/*' => Http::response([
                'status' => true,
                'data' => [
                    'apc_id' => "XXX-XXX",
                    'first_name' => "Juan",
                    'last_name' => "de la Cruz",
                    'email' => "jdcruz@apc.edu.ph"
                ],
                'method' => "GET"
            ], 200),
        ]);

        $response = $this->postJson('api/user/borrow-request/submit-V2', $requestData);
        $borrowedItems = BorrowedItem::count();
        $transaction = BorrowTransaction::all();

        // By this time, the endorser has been added to PAH Db
        $endorserResource = User::where('email', 'jdcruz@apc.edu.ph')->first();

        // Assert Controller Return Data
        $response->assertJsonStructure([
            'status',
            'message',
            'method'
        ]);
        $response->assertJson(self::getSuccessResponse(3));

        // Assert Transaction Values
        $this->assertEquals($transaction->first()->endorsed_by, $endorserResource->id);
        $this->assertEquals($borrowedItems, 18);
        $this->assertEquals($transaction->count(), 3);
    }



    // 05. two_duplicate_item_group_id_results_to failed validation
    public function test_two_duplicate_item_group_id_results_to_failed_validation()
    {
        $this->withoutMiddleware();
        $requestData = [
            'purpose' => "OTHERS",
            'user_defined_purpose' => 'sfsfs',
            "items" => [
                // ITRO
                [
                    'item_group_id' => $this->itemGroups['Canon 200d'],
                    'quantity' => 2,
                    'start_date' => (string) now()->addDay(),
                    'return_date' => (string) now()->addWeek(),
                ],
                [
                    'item_group_id' => $this->itemGroups['Canon 200d'],
                    'quantity' => 1,
                    'start_date' => (string) now()->addDay(),
                    'return_date' => (string) now()->addWeek(),
                ]
            ]
        ];

        $response = $this->postJson('api/user/borrow-request/submit-V2', $requestData);

        $response->assertJsonStructure([
            'status',
            'message',
            'errors',
            'method'
        ]);
        $this->assertEquals(false, $response['status']);
    }
}