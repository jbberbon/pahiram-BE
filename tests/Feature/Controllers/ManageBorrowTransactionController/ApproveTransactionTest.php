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
use App\Models\UserDepartment;
use App\Services\RetrieveStatusService\BorrowedItemStatusService;
use App\Services\RetrieveStatusService\BorrowTransactionStatusService;
use App\Utils\Constants\Statuses\ACCOUNT_STATUS;
use App\Utils\Constants\USER_ROLE;
use Http;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApproveTransactionTest extends TestCase
{
    use RefreshDatabase;
    protected $controller;
    protected $itroId;
    protected $supervisorRoleId;
    protected $itemGroups;

    protected $approvedBorrowedItemId;
    protected $pendingBorrowApprovalId;
    protected $disapprovedBorrowedItemId;


    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->seed();
        $this->itroId = Department::getIdBasedOnAcronym('ITRO');
        $this->supervisorRoleId = Role::getIdByRole('SUPERVISOR');
        $this->approvedBorrowedItemId = BorrowedItemStatusService::getApprovedStatusId();
        $this->pendingBorrowApprovalId = BorrowedItemStatusService::getPendingStatusId();
        $this->disapprovedBorrowedItemId = BorrowedItemStatusService::getDisapprovedStatusId();

        // Create users
        $borrower = User::factory()->create([
            'email' => 'borrower@student.apc.edu.ph'
        ]);
        $lendingEmp = User::factory()->create([
            'email' => 'lendingEmp@apc.edu.ph',
            'apc_id' => '20000-XXXX'
        ]);
        $supervisor = User::factory()->create([
            'email' => 'supervisor@apc.edu.ph',
            'apc_id' => '20001-XXXX',
            'user_role_id' => $this->supervisorRoleId
        ]);

        // Assign Supervisor to ITRO
        UserDepartment::create([
            'user_id' => $supervisor->id,
            'department_id' => $this->itroId
        ]);

        $this->itemGroups = [
            'Canon 200d' => ItemGroup::getIdByModelNameAndOfficeId('Canon 200d', $this->itroId),
            'Arduino Uno R4 WiFi' => ItemGroup::getIdByModelNameAndOfficeId('Arduino Uno R4 WiFi', $this->itroId),
        ];
        $requestedItems = [
            // ITRO
            [
                'item_group_id' => $this->itemGroups['Canon 200d'],
                'quantity' => 1,
                'start_date' => (string) now()->addDay(),
                'return_date' => (string) now()->addWeek(),
            ],
            [
                'item_group_id' => $this->itemGroups['Arduino Uno R4 WiFi'],
                'quantity' => 1,
                'start_date' => (string) now()->addDay(),
                'return_date' => (string) now()->addWeek(),
            ],
        ];
        $requestData = [
            'purpose' => "OTHERS",
            'user_defined_purpose' => 'sfsfs',
            "items" => $requestedItems,
        ];

        // Submit Borrow Request as borrower
        $this->actingAs($borrower);
        $this->postJson('api/user/borrow-request/submit-V2', $requestData);

        // Act as the lending from here on
        $this->actingAs($supervisor);
        // Instantiate the Controller being tested
        $this->controller = app(ManageBorrowingRequestController::class);
    }
    protected function tearDown(): void
    {
        // Delete all records from the tables
        parent::tearDown();
    }

    /*
     * 01. Able to approve all items
     * 02. Able to approve some items
     * 03. Able to disapprove some items
     * 04. Able to approve and disapprove at the same time
     */


    // 01. Able to approve all items
    public function test_able_to_approve_all_items(): void
    {
        // Prepare
        $transac = BorrowTransaction::first();
        $requestBody = ["approve_all_items" => true];

        // Act: Update Transaction
        $response = $this->patchJson("api/office/borrow-transaction/{$transac->id}/borrow-approval", $requestBody);
        // Assert
        $response->assertJsonStructure([
            'status',
            'message',
            'method'
        ]);
        $this->assertEquals(true, $response->json('status'));
        // \Log::info("Approve response", [$response]);

        // Act: Get Borrowed items
        $borrowedItems = BorrowedItem::all();
        $borrowedItemsStatusIds = $borrowedItems->pluck('borrowed_item_status_id');


        // Assert that all items have the same status ID
        $allItemsHaveSameStatus = $borrowedItemsStatusIds->every(function ($statusId) {
            return $statusId === $this->approvedBorrowedItemId;
        });
        $this->assertTrue($allItemsHaveSameStatus);

        // Assert that the transaction is Approved
        $transac = BorrowTransaction::first(); // Re-query to get updated value
        $this->assertEquals($transac->transac_status_id, BorrowTransactionStatusService::getApprovedTransactionId());
    }

    // 02. Able to approve some items
    public function test_able_to_approve_some_items(): void
    {
        // Prepare
        $transac = BorrowTransaction::first();

        // Fetch the first BorrowedItem and pluck the 'item_group_id' column
        $borrowedItem = BorrowedItem::first();
        $requestBody = [
            "items" => [
                [
                    'borrowed_item_id' => $borrowedItem->id,
                    'is_approved' => true
                ]
            ]
        ];

        // Act: Update Transaction
        $response = $this->patchJson("api/office/borrow-transaction/{$transac->id}/borrow-approval", $requestBody);
        // Assert
        $response->assertJsonStructure([
            'status',
            'message',
            'method'
        ]);
        $this->assertEquals(true, $response->json('status'));

        // Act: Get Borrowed items
        $borrowedItems = BorrowedItem::all();
        $borrowedItemsStatusIds = $borrowedItems->pluck('borrowed_item_status_id');

        // Assert that approvedBorrowedItemId exists in the array
        $this->assertContains(
            $this->pendingBorrowApprovalId,
            $borrowedItemsStatusIds->toArray()
        );

        // Assert that approvedBorrowedItemId exists in the array
        $this->assertContains(
            $this->approvedBorrowedItemId,
            $borrowedItemsStatusIds->toArray()
        );

        // Assert that the transaction is Pending Approval
        $transac = BorrowTransaction::first(); // Re-query to get updated value
        $this->assertEquals($transac->transac_status_id, BorrowTransactionStatusService::getPendingBorrowingApprovalTransactionId());
    }

    // 03. Able to disapprove some items
    public function test_able_to_disapprove_some_items(): void
    {
        // Prepare
        $transac = BorrowTransaction::first();

        // Fetch the first BorrowedItem and pluck the 'item_group_id' column
        $borrowedItem = BorrowedItem::first();
        $requestBody = [
            "items" => [
                [
                    'borrowed_item_id' => $borrowedItem->id,
                    'is_approved' => false
                ]
            ]
        ];

        // Act: Update Transaction
        $response = $this->patchJson("api/office/borrow-transaction/{$transac->id}/borrow-approval", $requestBody);
        // Assert
        $response->assertJsonStructure([
            'status',
            'message',
            'method'
        ]);
        $this->assertEquals(true, $response->json('status'));

        // Act: Get Borrowed items
        $borrowedItems = BorrowedItem::all();
        $borrowedItemsStatusIds = $borrowedItems->pluck('borrowed_item_status_id');

        // Assert that approvedBorrowedItemId exists in the array
        $this->assertContains(
            $this->pendingBorrowApprovalId,
            $borrowedItemsStatusIds->toArray()
        );

        // Assert that approvedBorrowedItemId exists in the array
        $this->assertContains(
            $this->disapprovedBorrowedItemId,
            $borrowedItemsStatusIds->toArray()
        );

        // Assert that the transaction is Pending Approval
        $transac = BorrowTransaction::first(); // Re-query to get updated value
        $this->assertEquals($transac->transac_status_id, BorrowTransactionStatusService::getPendingBorrowingApprovalTransactionId());
    }

    // 04. Able to approve and disapprove at the same time
    public function test_able_to_approve_disapprove_items(): void
    {
        // Prepare
        $transac = BorrowTransaction::first();

        // Fetch the first BorrowedItem and pluck the 'item_group_id' column
        $borrowedItemIds = BorrowedItem::all()->pluck('id');
        $requestBody = [
            "items" => [
                [
                    'borrowed_item_id' => $borrowedItemIds[0],
                    'is_approved' => false
                ],
                [
                    'borrowed_item_id' => $borrowedItemIds[1],
                    'is_approved' => true
                ]
            ]
        ];

        // Act: Update Transaction
        $response = $this->patchJson("api/office/borrow-transaction/{$transac->id}/borrow-approval", $requestBody);
        // Assert
        $response->assertJsonStructure([
            'status',
            'message',
            'method'
        ]);
        $this->assertEquals(true, $response->json('status'));

        // Act: Get Borrowed items
        $borrowedItems = BorrowedItem::all();
        $borrowedItemsStatusIds = $borrowedItems->pluck('borrowed_item_status_id');

        // Assert that approvedBorrowedItemId exists in the array
        $this->assertContains(
            $this->approvedBorrowedItemId,
            $borrowedItemsStatusIds->toArray()
        );

        // Assert that approvedBorrowedItemId exists in the array
        $this->assertContains(
            $this->disapprovedBorrowedItemId,
            $borrowedItemsStatusIds->toArray()
        );

        // Assert that the transaction is Pending Approval
        $transac = BorrowTransaction::first(); // Re-query to get updated value
        $this->assertEquals($transac->transac_status_id, BorrowTransactionStatusService::getApprovedTransactionId());
    }
}