<?php

namespace Tests\Feature\Controllers\ManageBorrowingRequestController;

use App\Http\Controllers\BorrowTransaction\ManageBorrowingRequestController;
use App\Models\BorrowedItem;
use App\Models\BorrowTransaction;
use App\Models\Department;
use App\Models\ItemGroup;
use App\Models\Role;
use App\Models\User;
use App\Models\UserDepartment;
use App\Services\RetrieveStatusService\BorrowedItemStatusService;
use App\Services\RetrieveStatusService\BorrowTransactionStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReleaseTransactionTest extends TestCase
{
    use RefreshDatabase;
    protected $controller;
    protected $itroId;
    protected $supervisorRoleId;
    protected $itemGroups;

    protected $inposessionBorrowedItemId;
    protected $pendingBorrowApprovalId;
    protected $unreleasedBorrowedItemId;
    protected $approvedBorrowedItemId;


    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->seed();
        $this->itroId = Department::getIdBasedOnAcronym('ITRO');
        $this->supervisorRoleId = Role::getIdByRole('SUPERVISOR');
        $this->inposessionBorrowedItemId = BorrowedItemStatusService::getInPossessionStatusId();
        $this->pendingBorrowApprovalId = BorrowedItemStatusService::getPendingStatusId();
        $this->unreleasedBorrowedItemId = BorrowedItemStatusService::getUnreleasedStatusId();
        $this->approvedBorrowedItemId = BorrowedItemStatusService::getApprovedStatusId();

        // Create users
        $borrowerEmployee = User::factory()->create([
            'email' => 'borrower@apc.edu.ph'
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
                'start_date' => (string) now()->addSeconds(1),
                'return_date' => (string) now()->addWeek(),
            ],
            [
                'item_group_id' => $this->itemGroups['Arduino Uno R4 WiFi'],
                'quantity' => 1,
                'start_date' => (string) now()->addSeconds(1),
                'return_date' => (string) now()->addWeek(),
            ],
        ];
        $requestData = [
            'purpose' => "OTHERS",
            'user_defined_purpose' => 'sfsfs',
            "items" => $requestedItems,
        ];

        // Submit Borrow Request as borrower
        $this->actingAs($borrowerEmployee);
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
     * 01. Able to release all items
     * 02. Able to release some items
     * 03. Able to withold some items
     * 04. Able to release and withold at the same time
     */


    // 01. Able to release all items
    public function test_able_to_approve_all_items(): void
    {
        // Prepare
        $transac = BorrowTransaction::first();
        $requestBody = ["release_all_items" => true];

        // Act: Update Transaction
        $response = $this->patchJson("api/office/borrow-transaction/{$transac->id}/release-item", $requestBody);
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


        // Assert that all items have the same status ID
        $allItemsHaveSameStatus = $borrowedItemsStatusIds->every(function ($statusId) {
            return $statusId === $this->inposessionBorrowedItemId;
        });
        $this->assertTrue($allItemsHaveSameStatus);

        // Assert that the transaction is Approved
        $transac = BorrowTransaction::first(); // Re-query to get updated value
        $this->assertEquals($transac->transac_status_id, BorrowTransactionStatusService::getOnGoingTransactionId());
    }


    // 02. Able to release some items
    public function test_able_to_release_some_items(): void
    {
        // Prepare
        $transac = BorrowTransaction::first();

        // Fetch the first BorrowedItem and pluck the 'item_group_id' column
        $borrowedItem = BorrowedItem::first();
        $requestBody = [
            "items" => [
                [
                    'borrowed_item_id' => $borrowedItem->id,
                    'is_released' => true
                ]
            ]
        ];


        // Act: Update Transaction
        sleep(1); // Delay in seconds
        $response = $this->patchJson("api/office/borrow-transaction/{$transac->id}/release-item", $requestBody);
        \Log::info('02 res', [$response]);

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

        // Assert that inpossession exists in the array
        $this->assertContains(
            $this->inposessionBorrowedItemId,
            $borrowedItemsStatusIds->toArray()
        );

        // Assert that approvedBorrowedItemId exists in the array
        $this->assertContains(
            $this->approvedBorrowedItemId,
            $borrowedItemsStatusIds->toArray()
        );

        // Assert that the transaction is Pending Approval
        $transac = BorrowTransaction::first(); // Re-query to get updated value
        $this->assertEquals($transac->transac_status_id, BorrowTransactionStatusService::getOnGoingTransactionId());
    }

    // 03. Able to withhold some items
    public function test_able_to_withhold_some_items(): void
    {
        // Prepare
        $transac = BorrowTransaction::first();

        // Fetch the first BorrowedItem and pluck the 'item_group_id' column
        $borrowedItem = BorrowedItem::first();
        $requestBody = [
            "items" => [
                [
                    'borrowed_item_id' => $borrowedItem->id,
                    'is_released' => false
                ]
            ]
        ];

        // Act: Update Transaction
        sleep(1); // Delay in seconds
        $response = $this->patchJson("api/office/borrow-transaction/{$transac->id}/release-item", $requestBody);
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

        // Assert that unreleased exists in the array
        $this->assertContains(
            $this->unreleasedBorrowedItemId,
            $borrowedItemsStatusIds->toArray()
        );

        // Assert that the transaction is Pending Approval
        $transac = BorrowTransaction::first(); // Re-query to get updated value
        $this->assertEquals($transac->transac_status_id, BorrowTransactionStatusService::getApprovedTransactionId());
    }


    // 04. Able to release and withold at the same time
    public function test_able_to_release_and_withhold_items(): void
    {
        // Prepare
        $transac = BorrowTransaction::first();

        // Fetch the first BorrowedItem and pluck the 'item_group_id' column
        $borrowedItemIds = BorrowedItem::all()->pluck('id');
        $requestBody = [
            "items" => [
                [
                    'borrowed_item_id' => $borrowedItemIds[0],
                    'is_released' => false
                ],
                [
                    'borrowed_item_id' => $borrowedItemIds[1],
                    'is_released' => true
                ]
            ]
        ];

        // Act: Update Transaction
        sleep(1);
        $response = $this->patchJson("api/office/borrow-transaction/{$transac->id}/release-item", $requestBody);
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

        // Assert that inpossesion exists in the array
        $this->assertContains(
            $this->inposessionBorrowedItemId,
            $borrowedItemsStatusIds->toArray()
        );

        // Assert that unreleased exists in the array
        $this->assertContains(
            $this->unreleasedBorrowedItemId,
            $borrowedItemsStatusIds->toArray()
        );

        // Assert that the transaction is Pending Approval
        $transac = BorrowTransaction::first(); // Re-query to get updated value
        $this->assertEquals($transac->transac_status_id, BorrowTransactionStatusService::getOnGoingTransactionId());
    }
}