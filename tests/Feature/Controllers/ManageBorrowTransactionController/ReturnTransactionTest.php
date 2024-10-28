<?php

namespace Tests\Feature\Controllers\ManageBorrowingRequestController;

use App\Http\Controllers\BorrowTransaction\ManageBorrowingRequestController;
use App\Models\BorrowedItem;
use App\Models\BorrowTransaction;
use App\Models\Department;
use App\Models\Item;
use App\Models\ItemGroup;
use App\Models\PenalizedTransaction;
use App\Models\Role;
use App\Models\User;
use App\Models\UserDepartment;
use App\Services\RetrieveStatusService\BorrowedItemStatusService;
use App\Services\RetrieveStatusService\BorrowTransactionStatusService;
use App\Services\RetrieveStatusService\ItemStatusService;
use App\Services\RetrieveStatusService\PenalizedTransactionStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReturnTransactionTest extends TestCase
{
    use RefreshDatabase;
    protected $transac;
    protected $controller;
    protected $itroId;


    protected $lendingEmployeeId;
    protected $itemGroups;
    protected $returnedBorrowedItemStatusId;
    protected $damagedBorrowedItemStatusId;
    protected $unrepairableBorrowedItemStatusId;
    protected $unreturnedBorrowedItemStatusId;

    protected $forRepairInventoryItemStatusId;
    protected $activeInventoryItemStatusId;
    protected $beyondRepairInventoryItemStatusId;
    protected $unreturnedInventoryItemStatusId;

    protected $pendingLendingSupApprovalStatusId;



    protected $completeTransactionStatusId;
    protected $unreturnedTransactionStatusId;


    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->seed();

        $this->itroId = Department::getIdBasedOnAcronym('ITRO');
        $this->lendingEmployeeId = Role::getIdByRole('LENDING_EMPLOYEE');
        $this->returnedBorrowedItemStatusId = BorrowedItemStatusService::getReturnedStatusId();
        $this->unreturnedBorrowedItemStatusId = BorrowedItemStatusService::getUnreturnedStatusId();
        $this->damagedBorrowedItemStatusId = BorrowedItemStatusService::getDamagedStatusId();
        $this->unrepairableBorrowedItemStatusId = BorrowedItemStatusService::getUnrepairableStatusId();

        $this->completeTransactionStatusId = BorrowTransactionStatusService::getCompletedTransactionId();
        $this->unreturnedTransactionStatusId = BorrowTransactionStatusService::getUnreturnedTransactionId();

        $this->pendingLendingSupApprovalStatusId = PenalizedTransactionStatusService::getPendingLendingSupervisorFinalization();

        $this->forRepairInventoryItemStatusId = ItemStatusService::getForRepairStatusId();
        $this->activeInventoryItemStatusId = ItemStatusService::getActiveStatusId();
        $this->beyondRepairInventoryItemStatusId = ItemStatusService::getBeyondRepairStatusId();
        $this->unreturnedInventoryItemStatusId = ItemStatusService::getUnreturnedRepairStatusId();


        // Create users
        $borrowerProf = User::factory()->create([
            'email' => 'borrowerprof@apc.edu.ph',
            'apc_id' => '20000-XXXX'
        ]);

        $itroEmployee = User::factory()->create([
            'email' => 'itro@apc.edu.ph',
            'apc_id' => '20001-XXXX',
            'user_role_id' => $this->lendingEmployeeId
        ]);

        // Assign Lending Employee to ITRO
        UserDepartment::create([
            'user_id' => $itroEmployee->id,
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
        $this->actingAs($borrowerProf);
        $this->postJson('api/user/borrow-request/submit-V2', $requestData);

        // Act as the lending from here on
        $this->actingAs($itroEmployee);

        // Release all items
        $this->transac = BorrowTransaction::first();

        $requestBody = ["release_all_items" => true];
        $this->patchJson("api/office/borrow-transaction/{$this->transac->id}/release-item", $requestBody);

        // Instantiate the Controller being tested
        $this->controller = app(ManageBorrowingRequestController::class);
    }

    protected function tearDown(): void
    {
        // Delete all records from the tables
        parent::tearDown();
    }

    /**
     * 01. Able to return all items in good condition
     * 02. Able to mark item as damaged with added penalty
     * 03. Able to mark item as unrepairable with added penalty
     * 04. Able to mark item as lost with added penalty
     * 05. Able to mark item as unreturned with added penalty
     * 06. Able to mark ALL item as unreturned with added penalty
     * 
     */

    // 01. Able to return all items in good condition
    public function test_able_to_return_all_items_in_good_condition()
    {
        // Prepare for retrurn
        $borrowedItems = BorrowedItem::all();
        $requestBody = [
            "items" => [
                [
                    'borrowed_item_id' => $borrowedItems[0]->id,
                    'item_status' => 'RETURNED',
                    'remarks_by_receiver' => "Everything ok, sfsfsfs"
                ],
                [
                    'borrowed_item_id' => $borrowedItems[1]->id,
                    'item_status' => 'RETURNED',
                    'remarks_by_receiver' => "Everything ok, sfsfsfs"
                ]
            ]
        ];

        // Act: Update Transaction
        $response = $this->patchJson("api/office/borrow-transaction/{$this->transac->id}/facilitate-item-return", $requestBody);
        // \Log::info('response', [$response]);
        // Assert
        $response->assertJsonStructure([
            'status',
            'message',
            'method'
        ]);
        $this->assertEquals(true, $response->json('status'));

        // Confirm that transaction status is complete
        $transacStatusId = BorrowTransaction::first()->transac_status_id;
        $this->assertEquals($this->completeTransactionStatusId, $transacStatusId);

        // Confirm items are returned
        $borrowedItemsStatusIds = BorrowedItem::all()->pluck('borrowed_item_status_id');
        // Assert that all items have the same status ID
        $allItemsHaveSameStatus = $borrowedItemsStatusIds->every(function ($statusId) {
            return $statusId === $this->returnedBorrowedItemStatusId;
        });
        $this->assertTrue($allItemsHaveSameStatus);
    }

    // 02. Able to mark item as damaged with added penalty
    public function test_able_to_mark_item_as_damaged_with_added_penalty()
    {
        // Prepare for retrurn
        $borrowedItems = BorrowedItem::all();
        $requestBody = [
            "items" => [
                [
                    'borrowed_item_id' => $borrowedItems[0]->id,
                    'item_status' => 'RETURNED',
                    'remarks_by_receiver' => "Everything ok, sfsfsfs"
                ],
                [
                    'borrowed_item_id' => $borrowedItems[1]->id,
                    'item_status' => 'DAMAGED_BUT_REPAIRABLE',
                    'penalty' => 5000,
                    'remarks_by_receiver' => "Everything NOT OK"
                ]
            ]
        ];

        // Act: Update Transaction
        $response = $this->patchJson("api/office/borrow-transaction/{$this->transac->id}/facilitate-item-return", $requestBody);
        // \Log::info('response', [$response]);
        // Assert
        $response->assertJsonStructure([
            'status',
            'message',
            'method'
        ]);
        $this->assertEquals(true, $response->json('status'));

        // Confirm that transaction status is complete
        $transac = BorrowTransaction::first();
        $transacStatusId = $transac->transac_status_id;
        $this->assertEquals($this->completeTransactionStatusId, $transacStatusId);

        // Confirm transaction is added to Penalized transaction with correct status
        $penalizedTransac = PenalizedTransaction::where('borrowing_transac_id', $transac->id)->first();
        $this->assertTrue($penalizedTransac->exists());

        // Confirm that penalized transac has a status of PENDING_LENDING_SUPERVISOR_APPROVAL
        $this->assertEquals($this->pendingLendingSupApprovalStatusId, $penalizedTransac->status_id);

        // Confirm Borrowed items have correct status
        $borrowedItems = BorrowedItem::all();
        $borrowedItemsStatusIds = $borrowedItems->pluck('borrowed_item_status_id');


        // Assert that RETURNED status exists in the array
        $this->assertContains(
            $this->returnedBorrowedItemStatusId,
            $borrowedItemsStatusIds->toArray()
        );

        // Assert that DAMAGED_BUT_REPAIRABLE exists in the array
        $this->assertContains(
            $this->damagedBorrowedItemStatusId,
            $borrowedItemsStatusIds->toArray()
        );

        // Assert that one item has 5000 penalty
        $penalties = $borrowedItems->pluck('penalty');
        $this->assertContains(
            "5000.00",
            $penalties->toArray()
        );

        // Confirm that Item in inventory is damaged
        $itemIds = $borrowedItems->pluck('item_id');
        $itemInventoryStatusIds = Item::whereIn('id', $itemIds)->pluck('item_status_id');

        // Assert that one item is still active
        $this->assertContains(
            $this->forRepairInventoryItemStatusId,
            $itemInventoryStatusIds->toArray()
        );

        // Assert that one item is FOR REPAIR
        $this->assertContains(
            $this->activeInventoryItemStatusId,
            $itemInventoryStatusIds->toArray()
        );
    }

    // 03. Able to mark item as unrepairable with added penalty
    public function test_able_to_mark_item_as_unrepairable_with_added_penalty()
    {
        // Prepare for retrurn
        $borrowedItems = BorrowedItem::all();
        $requestBody = [
            "items" => [
                [
                    'borrowed_item_id' => $borrowedItems[0]->id,
                    'item_status' => 'RETURNED',
                    'remarks_by_receiver' => "Everything ok, sfsfsfs"
                ],
                [
                    'borrowed_item_id' => $borrowedItems[1]->id,
                    'item_status' => 'UNREPAIRABLE',
                    'penalty' => 5000,
                    'remarks_by_receiver' => "Everything NOT OK"
                ]
            ]
        ];

        // Act: Update Transaction
        $response = $this->patchJson("api/office/borrow-transaction/{$this->transac->id}/facilitate-item-return", $requestBody);
        // \Log::info('response', [$response]);
        // Assert
        $response->assertJsonStructure([
            'status',
            'message',
            'method'
        ]);
        $this->assertEquals(true, $response->json('status'));

        // Confirm that transaction status is complete
        $transac = BorrowTransaction::first();
        $transacStatusId = $transac->transac_status_id;
        $this->assertEquals($this->completeTransactionStatusId, $transacStatusId);

        // Confirm transaction is added to Penalized transaction with correct status
        $penalizedTransac = PenalizedTransaction::where('borrowing_transac_id', $transac->id)->first();
        $this->assertTrue($penalizedTransac->exists());

        // Confirm that penalized transac has a status of PENDING_LENDING_SUPERVISOR_APPROVAL
        $this->assertEquals($this->pendingLendingSupApprovalStatusId, $penalizedTransac->status_id);

        // Confirm Borrowed items have correct status
        $borrowedItems = BorrowedItem::all();
        $borrowedItemsStatusIds = $borrowedItems->pluck('borrowed_item_status_id');


        // Assert that RETURNED status exists in the array
        $this->assertContains(
            $this->returnedBorrowedItemStatusId,
            $borrowedItemsStatusIds->toArray()
        );

        // Assert that DAMAGED_BUT_REPAIRABLE exists in the array
        $this->assertContains(
            $this->unrepairableBorrowedItemStatusId,
            $borrowedItemsStatusIds->toArray()
        );

        // Assert that one item has 5000 penalty
        $penalties = $borrowedItems->pluck('penalty');
        $this->assertContains(
            "5000.00",
            $penalties->toArray()
        );

        // Confirm that Item in inventory is damaged
        $itemIds = $borrowedItems->pluck('item_id');
        $itemInventoryStatusIds = Item::whereIn('id', $itemIds)->pluck('item_status_id');

        // Assert that one item is still active
        $this->assertContains(
            $this->beyondRepairInventoryItemStatusId,
            $itemInventoryStatusIds->toArray()
        );

        // Assert that one item is FOR REPAIR
        $this->assertContains(
            $this->activeInventoryItemStatusId,
            $itemInventoryStatusIds->toArray()
        );
    }

    // 06. Able to mark ALL item as unreturned with added penalty
    public function test_able_to_mark_all_item_as_unreturned_with_added_penalty()
    {
        // Prepare for retrurn
        $borrowedItems = BorrowedItem::all();
        $requestBody = [
            "items" => [
                [
                    'borrowed_item_id' => $borrowedItems[0]->id,
                    'item_status' => 'UNRETURNED',
                    'penalty' => 10000,
                    'remarks_by_receiver' => "Everything NOT ok, sfsfsfs"
                ],
                [
                    'borrowed_item_id' => $borrowedItems[1]->id,
                    'item_status' => 'UNRETURNED',
                    'penalty' => 5000,
                    'remarks_by_receiver' => "Everything NOT OK"
                ]
            ]
        ];

        // Act: Update Transaction
        $response = $this->patchJson("api/office/borrow-transaction/{$this->transac->id}/facilitate-item-return", $requestBody);
        // \Log::info('response', [$response]);
        // Assert
        $response->assertJsonStructure([
            'status',
            'message',
            'method'
        ]);
        $this->assertEquals(true, $response->json('status'));

        // Confirm that transaction status is UNRETURNED
        $transac = BorrowTransaction::first();
        $transacStatusId = $transac->transac_status_id;
        $this->assertEquals($this->unreturnedTransactionStatusId, $transacStatusId);

        // Confirm transaction is added to Penalized transaction with correct status
        $penalizedTransac = PenalizedTransaction::where('borrowing_transac_id', $transac->id)->first();
        $this->assertTrue($penalizedTransac->exists());

        // Confirm that penalized transac has a status of PENDING_LENDING_SUPERVISOR_APPROVAL
        $this->assertEquals($this->pendingLendingSupApprovalStatusId, $penalizedTransac->status_id);

        // Confirm items are UNRETURNED
        $borrowedItems = BorrowedItem::all();
        $borrowedItemsStatusIds = $borrowedItems->pluck('borrowed_item_status_id');

        // Assert that all items have the same UNRETURNED status ID
        $allItemsHaveSameStatus = $borrowedItemsStatusIds->every(function ($statusId) {
            return $statusId === $this->unreturnedBorrowedItemStatusId;
        });
        $this->assertTrue($allItemsHaveSameStatus);

        // Assert that one item has 5000 penalty
        $penalties = $borrowedItems->pluck('penalty');
        $this->assertContains(
            "5000.00",
            $penalties->toArray()
        );
        $this->assertContains(
            "10000.00",
            $penalties->toArray()
        );

        // Confirm that Item in inventory is UNRETURNED
        $itemIds = $borrowedItems->pluck('item_id');
        $itemInventoryStatusIds = Item::whereIn('id', $itemIds)->pluck('item_status_id');

        // Assert that ALL items are unreturned
        $allItemsHaveSameStatus = $itemInventoryStatusIds->every(function ($statusId) {
            return $statusId === $this->unreturnedInventoryItemStatusId;
        });
        $this->assertTrue($allItemsHaveSameStatus);
    }
}