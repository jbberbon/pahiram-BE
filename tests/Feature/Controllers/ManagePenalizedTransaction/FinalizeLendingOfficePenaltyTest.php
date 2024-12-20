<?php

namespace Tests\Feature\Controllers\ManageBorrowingRequestController;

use App\Http\Controllers\Penalty\ManagePenalizedLendingTransactionController;
use App\Models\BorrowedItem;
use App\Models\BorrowTransaction;
use App\Models\Department;
use App\Models\ItemGroup;
use App\Models\PenalizedTransaction;
use App\Models\Role;
use App\Models\User;
use App\Models\UserDepartment;
use App\Services\RetrieveStatusService\BorrowedItemStatusService;
use App\Services\RetrieveStatusService\BorrowTransactionStatusService;
use App\Services\RetrieveStatusService\ItemStatusService;
use App\Services\RetrieveStatusService\PenalizedTransactionStatusService;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinalizeLendingOfficePenaltyTest extends TestCase
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
    protected $completeTransactionStatusId;
    protected $unreturnedTransactionStatusId;

    protected $pendingLendingSupApprovalStatusId;

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

        $this->forRepairInventoryItemStatusId = ItemStatusService::getForRepairStatusId();
        $this->activeInventoryItemStatusId = ItemStatusService::getActiveStatusId();
        $this->beyondRepairInventoryItemStatusId = ItemStatusService::getBeyondRepairStatusId();
        $this->unreturnedInventoryItemStatusId = ItemStatusService::getUnreturnedRepairStatusId();

        $this->pendingLendingSupApprovalStatusId = PenalizedTransactionStatusService::getPendingLendingSupervisorFinalization();

        // Create users
        $borrowerProf = User::factory()->create([
            'email' => 'borrowerprof@apc.edu.ph',
            'apc_id' => '20000-XXXX'
        ]);

        $supervisor = User::factory()->create([
            'email' => 'itro@apc.edu.ph',
            'apc_id' => '20001-XXXX',
            'user_role_id' => $this->lendingEmployeeId
        ]);

        // Assign Lending Employee to ITRO
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
        $this->actingAs($borrowerProf);
        $this->postJson('api/user/borrow-request/submit-V2', $requestData);

        // Make Transaction Penalized and also the borrowed items
        $this->transac = BorrowTransaction::first();
        $this->transac->update([
            'transac_status_id' => $this->completeTransactionStatusId
        ]);
        BorrowedItem::query()->update([
            'penalty' => 2500.00,
            'receiver_id' => $supervisor->id,
            'borrowed_item_status_id' => $this->damagedBorrowedItemStatusId
        ]);
        PenalizedTransaction::create([
            'borrowing_transac_id' => $this->transac->id,
            'status_id' => $this->pendingLendingSupApprovalStatusId
        ]);

        // Act as the lending from here on
        $this->actingAs($supervisor);

        // Instantiate the Controller being tested
        $this->controller = app(ManagePenalizedLendingTransactionController::class);
    }

    protected function tearDown(): void
    {
        // Delete all records from the tables
        parent::tearDown();
    }

    /**
     * 01. Able to finalize penalty of single item without change in amt
     * 02. Able to finalize penalty of single item with change in amt
     * 03. Able to finalize penalty of all item without change in amt
     * 04. Able to finalize penalty of all item with change in amt
     * 
     */

    // 01. Able to finalize penalty amount of single item without change in amt
    public function test_able_to_finalize_penalty_of_single_item_without_change_in_amt()
    {
        // Prepare the payload for the api
        $borrowedItem = BorrowedItem::first();
        $inputRemarks = 'FINALIZED FINALIZED FINALIZED';
        $requestBody = [
            'items' => [
                [
                    'borrowed_item_id' => $borrowedItem->id,
                    'no_penalty_amt_change' => true,
                    'remarks_by_penalty_finalizer' => $inputRemarks
                ]
            ]
        ];

        // ACT: Submit request
        $response = $this->patchJson("api/office/finalize-penalty/{$this->transac->id}/penalized-borrow-transaction", $requestBody);

        // Assert
        $response->assertJsonStructure([
            'status',
            'message',
            'method'
        ]);
        $this->assertEquals(true, $response->json('status'));

        // Confirm that penalty amt did not change
        $borrowedItems = BorrowedItem::all();
        $penalties = $borrowedItems->pluck('penalty');

        $allItemsHavePenalty = $penalties->every(function ($penalty) {
            return $penalty === '2500.00';
        });
        $this->assertTrue($allItemsHavePenalty);

        // Confirm the remarks in one item has been updated
        $remarks = $borrowedItems->pluck('remarks_by_penalty_finalizer');
        $this->assertContains(
            $inputRemarks,
            $remarks->toArray()
        );

        // Confirm that ID of finalizer has been logged also
        $finalizer = $borrowedItems->pluck('penalty_finalized_by');
        // \Log::info('FINALIZERs', [$finalizer]);
        $this->assertContains(
            Auth::id(),
            $finalizer->toArray()
        );

    }



    // 02. Able to finalize penalty amount of single item with change in amt
    public function test_able_to_finalize_penalty_of_single_item_with_change_in_amt()
    {
        // Prepare the payload for the api
        $borrowedItem = BorrowedItem::first();
        $inputRemarks = 'FINALIZED FINALIZED FINALIZED';
        $inputPenalty = 500;
        $requestBody = [
            'items' => [
                [
                    'borrowed_item_id' => $borrowedItem->id,
                    'penalty' => $inputPenalty,
                    'remarks_by_penalty_finalizer' => $inputRemarks
                ]
            ]
        ];

        // ACT: Submit request
        $response = $this->patchJson("api/office/finalize-penalty/{$this->transac->id}/penalized-borrow-transaction", $requestBody);

        // Assert
        $response->assertJsonStructure([
            'status',
            'message',
            'method'
        ]);
        $this->assertEquals(true, $response->json('status'));

        // Confirm that penalty amt did change
        $borrowedItems = BorrowedItem::all();
        $penalties = $borrowedItems->pluck('penalty');
        \Log::info('PENALTY TC2', [$penalties]);
        $this->assertContains(
            "500.00",
            $penalties->toArray()
        );

        // Confirm the remarks in one item has been updated
        $remarks = $borrowedItems->pluck('remarks_by_penalty_finalizer');
        $this->assertContains(
            $inputRemarks,
            $remarks->toArray()
        );

        // Confirm that ID of finalizer has been logged also
        $finalizer = $borrowedItems->pluck('penalty_finalized_by');
        $this->assertContains(
            Auth::id(),
            $finalizer->toArray()
        );

        // Confirm that total penalty in transac changed
        $totalPenalty = BorrowTransaction::first()->penalty;
        $this->assertEquals($totalPenalty, '3000.00');
    }

    // 03. Able to finalize penalty of all item without change in amt
    public function test_able_to_finalize_penalty_of_all_item_without_change_in_amt()
    {
        // Prepare the payload for the api
        $borrowedItems = BorrowedItem::all();
        $borrowedItemIds = $borrowedItems->pluck('id')->toArray();
        $inputRemarks = 'FINALIZED FINALIZED FINALIZED';
        $requestBody = [
            'items' => [
                [
                    'borrowed_item_id' => $borrowedItemIds[0],
                    'no_penalty_amt_change' => true,
                    'remarks_by_penalty_finalizer' => $inputRemarks
                ],
                [
                    'borrowed_item_id' => $borrowedItemIds[1],
                    'no_penalty_amt_change' => true,
                    'remarks_by_penalty_finalizer' => $inputRemarks
                ]
            ]
        ];

        // ACT: Submit request
        $response = $this->patchJson("api/office/finalize-penalty/{$this->transac->id}/penalized-borrow-transaction", $requestBody);

        // Assert
        $response->assertJsonStructure([
            'status',
            'message',
            'method'
        ]);
        $this->assertEquals(true, $response->json('status'));

        // Confirm that penalty amt did not change
        $borrowedItems = BorrowedItem::all();
        $penalties = $borrowedItems->pluck('penalty');

        $allItemsHavePenalty = $penalties->every(function ($penalty) {
            return $penalty === '2500.00';
        });
        $this->assertTrue($allItemsHavePenalty);

        // Confirm the remarks in all items has been updated
        $remarks = $borrowedItems->pluck('remarks_by_penalty_finalizer');
        $allItemsHaveRemarks = $remarks->every(function ($remark) {
            return $remark === 'FINALIZED FINALIZED FINALIZED';
        });
        $this->assertTrue($allItemsHaveRemarks);

        // Confirm that ID of finalizer has been logged also
        $finalizers = $borrowedItems->pluck('penalty_finalized_by');
        // \Log::info('FINALIZERs', [$finalizer]);
        $allItemsHaveFinalizers = $finalizers->every(function ($finalizer) {
            return $finalizer === Auth::id();
        });
        $this->assertTrue($allItemsHaveFinalizers);
    }

    // 04. Able to finalize penalty of all item with change in amt
    public function test_able_to_finalize_penalty_of_all_item_with_change_in_amt()
    {
        // Prepare the payload for the api
        $borrowedItems = BorrowedItem::all();
        $borrowedItemIds = $borrowedItems->pluck('id')->toArray();
        $inputRemarks = 'FINALIZED FINALIZED FINALIZED';
        $requestBody = [
            'items' => [
                [
                    'borrowed_item_id' => $borrowedItemIds[0],
                    'penalty' => 500,
                    'remarks_by_penalty_finalizer' => $inputRemarks
                ],
                [
                    'borrowed_item_id' => $borrowedItemIds[1],
                    'penalty' => 500,
                    'remarks_by_penalty_finalizer' => $inputRemarks
                ]
            ]
        ];

        // ACT: Submit request
        $response = $this->patchJson("api/office/finalize-penalty/{$this->transac->id}/penalized-borrow-transaction", $requestBody);

        // Assert
        $response->assertJsonStructure([
            'status',
            'message',
            'method'
        ]);
        $this->assertEquals(true, $response->json('status'));

        // Confirm that penalty amt DID change
        $borrowedItems = BorrowedItem::all();
        $penalties = $borrowedItems->pluck('penalty');

        $allItemsHavePenalty = $penalties->every(function ($penalty) {
            return $penalty === '500.00';
        });
        $this->assertTrue($allItemsHavePenalty);

        // Confirm the remarks in all items has been updated
        $remarks = $borrowedItems->pluck('remarks_by_penalty_finalizer');
        $allItemsHaveRemarks = $remarks->every(function ($remark) {
            return $remark === 'FINALIZED FINALIZED FINALIZED';
        });
        $this->assertTrue($allItemsHaveRemarks);

        // Confirm that ID of finalizer has been logged also
        $finalizers = $borrowedItems->pluck('penalty_finalized_by');
        $allItemsHaveFinalizers = $finalizers->every(function ($finalizer) {
            return $finalizer === Auth::id();
        });
        $this->assertTrue($allItemsHaveFinalizers);


        // Confirm that total penalty in transac changed
        $totalPenalty = BorrowTransaction::first()->penalty;
        $this->assertEquals($totalPenalty, '1000.00');
    }
}