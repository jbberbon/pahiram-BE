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

class ApproveEndorsementTest extends TestCase
{
    use RefreshDatabase;
    protected $controller;
    protected $itroId;
    protected $supervisorRoleId;
    protected $itemGroups;

    protected $approvedBorrowedItemId;
    protected $pendingBorrowedItemApprovalId;
    protected $disapprovedBorrowedItemId;

    protected $pendingBorrowTransacApproval;
    protected $disapprovedTransac;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->seed();
        $this->itroId = Department::getIdBasedOnAcronym('ITRO');
        $this->supervisorRoleId = Role::getIdByRole('SUPERVISOR');
        $this->approvedBorrowedItemId = BorrowedItemStatusService::getApprovedStatusId();
        $this->pendingBorrowedItemApprovalId = BorrowedItemStatusService::getPendingStatusId();
        $this->disapprovedBorrowedItemId = BorrowedItemStatusService::getDisapprovedStatusId();

        $this->pendingBorrowTransacApproval = BorrowTransactionStatusService::getPendingBorrowingApprovalTransactionId();
        $this->disapprovedTransac = BorrowTransactionStatusService::getDisapprovedTransactionId();

        // Create users
        $borrower = User::factory()->create([
            'email' => 'borrower@student.apc.edu.ph'
        ]);
        $endorser = User::factory()->create([
            'email' => 'endorser@apc.edu.ph',
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
            'endorsed_by' => $endorser->apc_id,
            'apcis_token' => '8|rWOpxuoj6HaszyBhCDvk4MKFqbAnAFx6AZvOJk4D6b40e168'
        ];

        // Submit Borrow Request as borrower
        $this->actingAs($borrower);
        $this->postJson('api/user/borrow-request/submit-V2', $requestData);

        // Act as the lending from here on
        $this->actingAs($endorser);
        // Instantiate the Controller being tested
        $this->controller = app(ManageBorrowingRequestController::class);
    }

    /**
     *  01. Able to approve endorsement
     *  02. Able to disapprove endorsement
     */

    // 01. Able to approve endorsement
    public function test_able_to_approve_endorsement(): void
    {
        // Prepare
        $transac = BorrowTransaction::first();
        $requestBody = ["approval" => true];

        // Act: Update Transaction
        $response = $this->patchJson("api/endorsement/{$transac->id}/approval", $requestBody);
        \Log::info("res", [$response]);
        // Assert
        $response->assertJsonStructure([
            'status',
            'message',
            'method'
        ]);
        $this->assertEquals(true, $response->json('status'));

        // Act: Get updated transaction
        $transac = BorrowTransaction::first();

        // Assert that transaction status is pending borrowing approval
        $this->assertEquals($transac->transac_status_id, $this->pendingBorrowTransacApproval);
    }

    // 02. Able to disapprove endorsement
    public function test_able_to_disapprove_endorsement(): void
    {
        // Prepare
        $transac = BorrowTransaction::first();
        $requestBody = ["approval" => false];

        // Act: Update Transaction
        $response = $this->patchJson("api/endorsement/{$transac->id}/approval", $requestBody);
        \Log::info("res", [$response]);
        // Assert
        $response->assertJsonStructure([
            'status',
            'message',
            'method'
        ]);
        $this->assertEquals(true, $response->json('status'));

        // Act: Get updated transaction
        $transac = BorrowTransaction::first();

        // Assert that transaction status is disapproved
        $this->assertEquals($transac->transac_status_id, $this->disapprovedTransac);

        // Act: Get the updated Borrowed items
        $borrowedItemsStatuses = BorrowedItem::all()->pluck('borrowed_item_status_id');

        \Log::info("item statuses", [$borrowedItemsStatuses]);
        \Log::info('disapproved id', [$this->disapprovedBorrowedItemId]);

        // Assert that every status is equal to the expected value
        $disapprovedBorrowedItemId = $this->disapprovedBorrowedItemId;
        $this->assertTrue(
            $borrowedItemsStatuses->every(function ($statusId) use ($disapprovedBorrowedItemId) {
                return $statusId === $disapprovedBorrowedItemId;
            })
        );



    }
}