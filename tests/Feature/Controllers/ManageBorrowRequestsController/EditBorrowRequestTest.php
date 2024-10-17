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
use App\Services\RetrieveStatusService\BorrowedItemStatusService;
use App\Utils\Constants\Statuses\ACCOUNT_STATUS;
use App\Utils\Constants\USER_ROLE;
use Http;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditBorrowRequestTest extends TestCase
{
    use RefreshDatabase;
    protected $apcisUrl;
    protected $controller;
    protected $itemGroups;


    protected $itroId;
    protected $esloId;
    protected $bmoId;
    protected $pendingApprovalStatusId;
    protected $cancelledStatusId;


    // Default New User Data
    protected $borrowerRoleId;
    protected $activeAccStatusId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->seed();


        // Create a user
        $user = User::factory()->create();
        // Log the user in
        $this->actingAs($user);

        // Instantiate Controller Class
        $this->controller = app(ManageBorrowingRequestController::class);


        $this->itroId = Department::getIdBasedOnAcronym('ITRO');
        $this->esloId = Department::getIdBasedOnAcronym('ESLO');
        $this->bmoId = Department::getIdBasedOnAcronym('BMO');
        // Create necessary item groups
        $this->itemGroups = [
            'Canon 200d' => ItemGroup::getIdByModelNameAndOfficeId('Canon 200d', $this->itroId),
            'MacBook Air M1' => ItemGroup::getIdByModelNameAndOfficeId('MacBook Air M1', $this->itroId),
            'Arduino Uno R4 WiFi' => ItemGroup::getIdByModelNameAndOfficeId('Arduino Uno R4 WiFi', $this->itroId),
            'FLUKE T150 Voltage Tester' => ItemGroup::getIdByModelNameAndOfficeId('FLUKE T150 Voltage Tester', $this->esloId),
            'Micron Cresta ZS Microscope' => ItemGroup::getIdByModelNameAndOfficeId('Micron Cresta ZS Microscope', $this->esloId),
            'Lifetime 6ft Folding Table' => ItemGroup::getIdByModelNameAndOfficeId('Lifetime 6ft Folding Table', $this->bmoId),
            'Spalding FIBA 2007' => ItemGroup::getIdByModelNameAndOfficeId('Spalding FIBA 2007', $this->bmoId),
        ];

        $this->pendingApprovalStatusId = BorrowedItemStatusService::getPendingStatusId();
        $this->cancelledStatusId = BorrowedItemStatusService::getCancelledStatusId();

        $this->borrowerRoleId = Role::getIdByRole(USER_ROLE::BORROWER);
        $this->activeAccStatusId = AccountStatus::getIdByStatus(ACCOUNT_STATUS::ACTIVE);
        $this->apcisUrl = env('APCIS_URL'); // Initialize the baseURL property
    }

    protected function tearDown(): void
    {
        // Delete all records from the tables
        parent::tearDown();
    }

    /*
     * 01. Edit Transaction data with no item data change
     * 01.01. Able to tag endorser that already exists in pahiram db
     * 01.02. Able to tag endorser that not yet exist in pahiram db
     * 02. Subtract quantities of 3 item groups
     * 03. Add Item Quantities of 3 item groups
     * 04. Edit Item Date
     * 05. Edit Item Date and Qtys
     * 06. Edit Both transaction data and Item Date and Qty
     */


    // 01. Edit Transaction data with no item data change
    public function test_able_to_edit_transaction_with_no_item_data_change(): void
    {
        $this->withoutMiddleware(); // Skips all middleware

        // Prepare
        // --> submit the borrow transaction for this tc
        $requestData = [
            'purpose' => "OTHERS",
            'user_defined_purpose' => 'sfsfs',
            "items" => [
                // ITRO
                [
                    'item_group_id' => $this->itemGroups['Canon 200d'],
                    'quantity' => 3,
                    'start_date' => (string) now()->addDay(),
                    'return_date' => (string) now()->addWeek(),
                ]
            ]
        ];
        $this->postJson('api/user/borrow-request/submit-V2', $requestData);

        // Edit the transaction
        $borrowTransaction = BorrowTransaction::latest('created_at')->first();

        $editedData = [
            'request_data' => [
                // 'endorsed_by' => '',
                // 'apcis_token' => '',
                'purpose' => 'ACADEMIC_REQUIREMENT',
                'user_defined_purpose' => 'edited purpose'
            ]
        ];

        $editResponse = $this->patchJson('api/user/borrow-request/' . $borrowTransaction->id . '/edit', $editedData);
        $updatedBorrowTransaction = BorrowTransaction::latest('created_at')->first();

        // Assert 
        $editResponse->assertJsonStructure([
            'status',
            'message',
            'method'
        ]);
        $this->assertEquals($editedData['request_data']['user_defined_purpose'], $updatedBorrowTransaction->user_defined_purpose);
        $this->assertEquals(true, $editResponse['status']);
    }

    // 01.01. Able to tag endorser that already exists in pahiram db
    public function test_able_to_tag_endorser_that_already_exists_in_pah_db(): void
    {
        $this->withoutMiddleware(); // Skips all middleware

        // Submit the request
        $requestData = [
            'purpose' => "OTHERS",
            'user_defined_purpose' => 'sfsfs',
            "items" => [
                // ITRO
                [
                    'item_group_id' => $this->itemGroups['Canon 200d'],
                    'quantity' => 3,
                    'start_date' => (string) now()->addDay(),
                    'return_date' => (string) now()->addWeek(),
                ]
            ]
        ];
        $this->postJson('api/user/borrow-request/submit-V2', $requestData);
        $borrowTransaction = BorrowTransaction::latest('created_at')->first();

        // Create the dummy endorser
        $endorserResource = User::create([
            'apc_id' => 'XXX-XXX',
            'first_name' => 'Juan',
            'last_name' => "de la Cruz",
            'email' => "jdcruz@apc.edu.ph",
            'user_role_id' => $this->borrowerRoleId,
            'acc_status_id' => $this->activeAccStatusId
        ]);

        // Edit the transaction
        $editedData = [
            'request_data' => [
                'endorsed_by' => 'XXX-XXX',
                'apcis_token' => '1306|WeLWJb5xAdtAhZ5FUSIz5MgIzPgiSvG84botQ8yv8070c589' // Just a sample
            ]
        ];

        $editResponse = $this
            ->patchJson(
                'api/user/borrow-request/' . $borrowTransaction->id . '/edit',
                $editedData
            );
        $updatedBorrowTransaction = BorrowTransaction::latest('created_at')->first();

        // Assert Controller Return Data
        $editResponse->assertJsonStructure([
            'status',
            'message',
            'method'
        ]);
        $this->assertEquals(true, $editResponse['status']);

        // Assert Transaction Values
        $this->assertEquals($updatedBorrowTransaction->endorsed_by, $endorserResource->id);
    }

    // 01.02. Able to tag endorser that not yet exist in pahiram db
    public function test_able_to_tag_endorser_that_not_yet_exist_in_pah_db(): void
    {
        $this->withoutMiddleware(); // Skips all middleware

        // Fake HTTP
        Http::fake([
            $this->apcisUrl . '/users/exists/*' => Http::response([
                'status' => true,
                'data' => true,
                'method' => "GET"
            ], 200),
        ]);
        Http::fake([
            $this->apcisUrl . '/users/*' => Http::response([
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
        // Submit the request
        $requestData = [
            'purpose' => "OTHERS",
            'user_defined_purpose' => 'sfsfs',
            "items" => [
                // ITRO
                [
                    'item_group_id' => $this->itemGroups['Canon 200d'],
                    'quantity' => 3,
                    'start_date' => (string) now()->addDay(),
                    'return_date' => (string) now()->addWeek(),
                ]
            ]
        ];
        $this->postJson('api/user/borrow-request/submit-V2', $requestData);
        $borrowTransaction = BorrowTransaction::latest('created_at')->first();

        // Edit the transaction
        $editedData = [
            'request_data' => [
                'endorsed_by' => 'XXX-XXX',
                'apcis_token' => '1306|WeLWJb5xAdtAhZ5FUSIz5MgIzPgiSvG84botQ8yv8070c589' // Just a sample
            ]
        ];

        $editResponse = $this
            ->patchJson(
                'api/user/borrow-request/' . $borrowTransaction->id . '/edit',
                $editedData
            );
        $updatedBorrowTransaction = BorrowTransaction::latest('created_at')->first();

        $endorserResource = User::where('email', 'jdcruz@apc.edu.ph')->first();        
        // Assert Controller Return Data
        $editResponse->assertJsonStructure([
            'status',
            'message',
            'method'
        ]);
        $this->assertEquals(true, $editResponse['status']);

        // Assert Transaction Values
        $this->assertEquals($updatedBorrowTransaction->endorsed_by, $endorserResource->id);
    }

    // 02. Subtract quantity of 3 item groups
    public function test_able_to_subtract_qty_of_3_item_groups()
    {
        $this->withoutMiddleware(); // Skips all middleware

        // Prepare
        // --> submit the borrow transaction for this tc
        $requestData = [
            'purpose' => "OTHERS",
            'user_defined_purpose' => 'sfsfs',
            "items" => [
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
                [
                    'item_group_id' => $this->itemGroups['MacBook Air M1'],
                    'quantity' => 3,
                    'start_date' => (string) now()->addDay(),
                    'return_date' => (string) now()->addWeek(),
                ]

            ]
        ];
        $this->postJson('api/user/borrow-request/submit-V2', $requestData);

        // Edit the transaction
        $borrowTransactionId = BorrowTransaction::latest('created_at')->value('id');

        $borrowedItemGroupIds = BorrowedItem::where('borrowing_transac_id', $borrowTransactionId)
            ->join('items', 'borrowed_items.item_id', '=', 'items.id')
            ->join('item_groups', 'items.item_group_id', '=', 'item_groups.id')
            ->distinct()
            ->pluck('item_groups.id')
            ->toArray();

        $editedData = [
            'edit_existing_items' => [
                [
                    'item_group_id' => $borrowedItemGroupIds[0],
                    'quantity' => 2
                ],
                [
                    'item_group_id' => $borrowedItemGroupIds[1],
                    'quantity' => 2
                ],
                [
                    'item_group_id' => $borrowedItemGroupIds[2],
                    'quantity' => 1
                ],
            ]
        ];

        $editResponse = $this->patchJson('api/user/borrow-request/' . $borrowTransactionId . '/edit', $editedData);

        $allBorrowedItemIds = BorrowedItem::where('borrowing_transac_id', $borrowTransactionId)
            ->get()
            ->pluck('id');

        $activeItemIds = BorrowedItem::where('borrowing_transac_id', $borrowTransactionId)
            ->where('borrowed_item_status_id', $this->pendingApprovalStatusId)
            ->get()
            ->pluck('id');

        $this->assertEquals(9, $allBorrowedItemIds->count());
        $this->assertEquals(5, $activeItemIds->count());
        $editResponse->assertJsonStructure([
            'status',
            'message',
            'method'
        ]);
        $this->assertEquals(true, $editResponse['status']);

    }

    // 03. Add Item Quantities of 3 item groups
    public function test_able_to_add_qty_of_3_item_groups()
    {
        $this->withoutMiddleware(); // Skips all middleware

        // Prepare
        // --> submit the borrow transaction for this tc
        $requestData = [
            'purpose' => "OTHERS",
            'user_defined_purpose' => 'sfsfs',
            "items" => [
                // ITRO
                [
                    'item_group_id' => $this->itemGroups['Canon 200d'],
                    'quantity' => 1,
                    'start_date' => (string) now()->addDay(),
                    'return_date' => (string) now()->addWeek(),
                ],
                [
                    'item_group_id' => $this->itemGroups['Arduino Uno R4 WiFi'],
                    'quantity' => 2,
                    'start_date' => (string) now()->addDay(),
                    'return_date' => (string) now()->addWeek(),
                ],
                [
                    'item_group_id' => $this->itemGroups['MacBook Air M1'],
                    'quantity' => 3,
                    'start_date' => (string) now()->addDay(),
                    'return_date' => (string) now()->addWeek(),
                ]

            ]
        ];
        $this->postJson('api/user/borrow-request/submit-V2', $requestData);

        // Edit the transaction
        $borrowTransactionId = BorrowTransaction::latest('created_at')->value('id');

        $borrowedItemGroupIds = BorrowedItem::where('borrowing_transac_id', $borrowTransactionId)
            ->join('items', 'borrowed_items.item_id', '=', 'items.id')
            ->join('item_groups', 'items.item_group_id', '=', 'item_groups.id')
            ->distinct()
            ->pluck('item_groups.id')
            ->toArray();

        $editedData = [
            'edit_existing_items' => [
                [
                    'item_group_id' => $borrowedItemGroupIds[0],
                    'quantity' => 4
                ],
                [
                    'item_group_id' => $borrowedItemGroupIds[1],
                    'quantity' => 4
                ],
                [
                    'item_group_id' => $borrowedItemGroupIds[2],
                    'quantity' => 4
                ],
            ]
        ];

        $editResponse = $this->patchJson('api/user/borrow-request/' . $borrowTransactionId . '/edit', $editedData);
        $activeItemIds = BorrowedItem::where('borrowing_transac_id', $borrowTransactionId)
            ->where('borrowed_item_status_id', $this->pendingApprovalStatusId)
            ->get()
            ->pluck('id');


        $this->assertEquals(12, $activeItemIds->count());
        $editResponse->assertJsonStructure([
            'status',
            'message',
            'method'
        ]);
        $this->assertEquals(true, $editResponse['status']);

    }

    // 04. Edit Item Date
    public function test_able_to_edit_item_group_date()
    {
        $this->withoutMiddleware(); // Skips all middleware

        // Prepare
        // --> submit the borrow transaction for this tc
        $requestData = [
            'purpose' => "OTHERS",
            'user_defined_purpose' => 'sfsfs',
            "items" => [
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
                [
                    'item_group_id' => $this->itemGroups['MacBook Air M1'],
                    'quantity' => 3,
                    'start_date' => (string) now()->addDay(),
                    'return_date' => (string) now()->addWeek(),
                ]

            ]
        ];
        $this->postJson('api/user/borrow-request/submit-V2', $requestData);

        // Edit the transaction
        $borrowTransactionId = BorrowTransaction::latest('created_at')->value('id');

        $borrowedItemGroupIds = BorrowedItem::where('borrowing_transac_id', $borrowTransactionId)
            ->join('items', 'borrowed_items.item_id', '=', 'items.id')
            ->join('item_groups', 'items.item_group_id', '=', 'item_groups.id')
            ->distinct()
            ->pluck('item_groups.id')
            ->toArray();

        $editedData = [
            'edit_existing_items' => [
                [
                    'item_group_id' => $borrowedItemGroupIds[0],
                    'start_date' => (string) now()->addWeek(),
                    'return_date' => (string) now()->addWeeks(2),
                    'quantity' => 3
                ],
                [
                    'item_group_id' => $borrowedItemGroupIds[1],
                    'start_date' => (string) now()->addWeek(),
                    'return_date' => (string) now()->addWeeks(2),
                    'quantity' => 3
                ],
                [
                    'item_group_id' => $borrowedItemGroupIds[2],
                    'start_date' => (string) now()->addWeek(),
                    'return_date' => (string) now()->addWeeks(2),
                    'quantity' => 3
                ],
            ]
        ];

        $editResponse = $this->patchJson('api/user/borrow-request/' . $borrowTransactionId . '/edit', $editedData);

        $allBorrowedItemIds = BorrowedItem::where('borrowing_transac_id', $borrowTransactionId)
            ->get()
            ->pluck('id');
        $activeItemIds = BorrowedItem::where('borrowing_transac_id', $borrowTransactionId)
            ->where('borrowed_item_status_id', $this->pendingApprovalStatusId)
            ->get()
            ->pluck('id');

        $this->assertEquals(9, $activeItemIds->count());
        $this->assertEquals(18, $allBorrowedItemIds->count());
        $editResponse->assertJsonStructure([
            'status',
            'message',
            'method'
        ]);
        $this->assertEquals(true, $editResponse['status']);

    }

    // 05. Edit Item Date and Qtys
    public function test_able_to_edit_item_group_date_and_qty()
    {
        $this->withoutMiddleware(); // Skips all middleware

        // Prepare
        // --> submit the borrow transaction for this tc
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
                    'item_group_id' => $this->itemGroups['Arduino Uno R4 WiFi'],
                    'quantity' => 1,
                    'start_date' => (string) now()->addDay(),
                    'return_date' => (string) now()->addWeek(),
                ],
                [
                    'item_group_id' => $this->itemGroups['MacBook Air M1'],
                    'quantity' => 4,
                    'start_date' => (string) now()->addDay(),
                    'return_date' => (string) now()->addWeek(),
                ]

            ]
        ];
        $this->postJson('api/user/borrow-request/submit-V2', $requestData);

        // Edit the transaction
        $borrowTransactionId = BorrowTransaction::latest('created_at')->value('id');

        $editedData = [
            'edit_existing_items' => [
                [
                    'item_group_id' => $this->itemGroups['Canon 200d'],
                    'start_date' => (string) now()->addWeek(),
                    'return_date' => (string) now()->addWeeks(2),
                    'quantity' => 3
                ],
                [
                    'item_group_id' => $this->itemGroups['Arduino Uno R4 WiFi'],
                    'start_date' => (string) now()->addWeek(),
                    'return_date' => (string) now()->addWeeks(2),
                    'quantity' => 3
                ],
                [
                    'item_group_id' => $this->itemGroups['MacBook Air M1'],
                    'start_date' => (string) now()->addWeek(),
                    'return_date' => (string) now()->addWeeks(2),
                    'quantity' => 3
                ],
            ]
        ];

        $editResponse = $this->patchJson('api/user/borrow-request/' . $borrowTransactionId . '/edit', $editedData);

        $allBorrowedItemIds = BorrowedItem::where('borrowing_transac_id', $borrowTransactionId)
            ->get()
            ->pluck('id');
        $activeItemIds = BorrowedItem::where('borrowing_transac_id', $borrowTransactionId)
            ->where('borrowed_item_status_id', $this->pendingApprovalStatusId)
            ->get()
            ->pluck('id');

        $cancelledItemIds = BorrowedItem::where('borrowing_transac_id', $borrowTransactionId)
            ->where('borrowed_item_status_id', $this->cancelledStatusId)
            ->get()
            ->pluck('id');


        $this->assertEquals(9, $activeItemIds->count());
        $this->assertEquals(7, $cancelledItemIds->count());
        $this->assertEquals(16, $allBorrowedItemIds->count());
        $editResponse->assertJsonStructure([
            'status',
            'message',
            'method'
        ]);
        $this->assertEquals(true, $editResponse['status']);

    }

    // 06. Edit Both transaction data and Item Date and Qty
    public function test_able_to_edit_both_transac_data_and_item_groups_and_date_and_qty()
    {
        $this->withoutMiddleware(); // Skips all middleware

        // Prepare
        // --> submit the borrow transaction for this tc
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
                    'item_group_id' => $this->itemGroups['Arduino Uno R4 WiFi'],
                    'quantity' => 1,
                    'start_date' => (string) now()->addDay(),
                    'return_date' => (string) now()->addWeek(),
                ],
                [
                    'item_group_id' => $this->itemGroups['MacBook Air M1'],
                    'quantity' => 4,
                    'start_date' => (string) now()->addDay(),
                    'return_date' => (string) now()->addWeek(),
                ]

            ]
        ];
        $this->postJson('api/user/borrow-request/submit-V2', $requestData);

        // Edit the transaction
        $borrowTransactionId = BorrowTransaction::latest('created_at')->value('id');

        $editedData = [
            'request_data' => [
                // 'endorsed_by' => '',
                // 'apcis_token' => '',
                'purpose' => 'ACADEMIC_REQUIREMENT',
                'user_defined_purpose' => 'edited purpose'
            ],
            'edit_existing_items' => [
                [
                    'item_group_id' => $this->itemGroups['Canon 200d'],
                    'start_date' => (string) now()->addWeek(),
                    'return_date' => (string) now()->addWeeks(2),
                    'quantity' => 1
                ],
                [
                    'item_group_id' => $this->itemGroups['Arduino Uno R4 WiFi'],
                    'start_date' => (string) now()->addWeek(),
                    'return_date' => (string) now()->addWeeks(2),
                    'quantity' => 2
                ],
                [
                    'item_group_id' => $this->itemGroups['MacBook Air M1'],
                    'start_date' => (string) now()->addWeek(),
                    'return_date' => (string) now()->addWeeks(2),
                    'quantity' => 3
                ],
            ]
        ];

        $editResponse = $this->patchJson('api/user/borrow-request/' . $borrowTransactionId . '/edit', $editedData);

        $allBorrowedItemIds = BorrowedItem::where('borrowing_transac_id', $borrowTransactionId)
            ->get()
            ->pluck('id');
        $activeItemIds = BorrowedItem::where('borrowing_transac_id', $borrowTransactionId)
            ->where('borrowed_item_status_id', $this->pendingApprovalStatusId)
            ->get()
            ->pluck('id');

        $cancelledItemIds = BorrowedItem::where('borrowing_transac_id', $borrowTransactionId)
            ->where('borrowed_item_status_id', $this->cancelledStatusId)
            ->get()
            ->pluck('id');
        $updatedBorrowTransaction = BorrowTransaction::latest('created_at')->first();

        $this->assertEquals($editedData['request_data']['user_defined_purpose'], $updatedBorrowTransaction->user_defined_purpose);

        $this->assertEquals(6, $activeItemIds->count());
        $this->assertEquals(7, $cancelledItemIds->count());
        $this->assertEquals(13, $allBorrowedItemIds->count());
        $editResponse->assertJsonStructure([
            'status',
            'message',
            'method'
        ]);
        $this->assertEquals(true, $editResponse['status']);

    }
}