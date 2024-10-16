<?php

namespace Tests\Unit\Services\BorrowRequestService\EditBorrowRequestService;

use App\Models\BorrowedItem;
use App\Models\BorrowedItemStatus;
use App\Models\BorrowPurpose;
use App\Models\User;
use App\Services\BorrowRequestService\EditBorrowRequestService;
use App\Services\RetrieveStatusService\BorrowedItemStatusService;
use Tests\TestCase;
use Mockery as m;
use Illuminate\Http\JsonResponse;
use App\Models\BorrowTransaction;

class ProcessToBeQtyChangedOnlyTest extends TestCase
{
    private $service;

    private $mockedBorrowedItem;
    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }
    protected function setUp(): void
    {
        parent::setUp();
        // Mock dependencies like BorrowedItem
        $this->mockedBorrowedItem = m::mock('alias:' . BorrowedItem::class);

        // Initialize the service instance
        $this->service = new EditBorrowRequestService();
    }

    /**
     * Test case where items are subtracted.
     */
    public function test_subtract_items()
    {
        // Arrange
        $transacId = 'transac123';
        $toBeQtyChangedOnly = [
            ['item_group_id' => '1', 'quantity' => 3],
            // Add other items if needed
        ];


        // Create a mock for BorrowedItem
        $borrowedItemMock = m::mock('alias:' . BorrowedItem::class);

        // Mock the static method with the expected parameters
        $borrowedItemMock->shouldReceive('getActiveModelItemQtyInTransaction')
            ->with($transacId, '1') // Ensure this matches the actual call
            ->andReturn(5); // Assuming the active quantity is 5 for the test

        // Act
        $result = $this->service->processToBeQtyChangedOnly($transacId, $toBeQtyChangedOnly);

        // Assert
        // Check that the logic correctly determines what to cancel and what to add
        $this->assertEquals([], $result['toBeCancelledBorrowedItemIds']); // Adjust based on logic
        $this->assertNotEmpty($result['itemsToBeAdded']); // Adjust based on expected output
    }



    /**
     * Test case where items are added.
     */
    public function test_add_items()
    {
        $transacId = 'transac123';
        $toBeQtyChangedOnly = [
            [
                'item_group_id' => 1,
                'quantity' => 6
            ]
        ];

        // Mock the return for getActiveModelItemQtyInTransaction
        $this->mockedBorrowedItem->shouldReceive('getActiveModelItemQtyInTransaction')
            ->with($transacId, 1)
            ->andReturn(3);

        // Mock the return for BorrowedItem::where(...)->select(...)->first()->toArray()
        $this->mockedBorrowedItem->shouldReceive('where->join->join->where->select->first->toArray')
            ->andReturn(['start_date' => '2024-10-10', 'return_date' => '2024-11-10']);

        $result = $this->service->processToBeQtyChangedOnly($transacId, $toBeQtyChangedOnly);

        $this->assertEmpty($result['toBeCancelledBorrowedItemIds']);
        $this->assertEquals([
            [
                'item_group_id' => 1,
                'start_date' => '2024-10-10',
                'return_date' => '2024-11-10',
                'quantity' => 3 // Absolute difference
            ]
        ], $result['itemsToBeAdded']);
    }

    /**
     * Test case where no items are changed.
     */
    public function test_no_changes()
    {
        $transacId = 'transac123';
        $toBeQtyChangedOnly = [
            [
                'item_group_id' => 1,
                'quantity' => 5
            ]
        ];

        // Mock the return for getActiveModelItemQtyInTransaction
        $this->mockedBorrowedItem->shouldReceive('getActiveModelItemQtyInTransaction')
            ->with($transacId, 1)
            ->andReturn(5);  // No change in quantity

        $result = $this->service->processToBeQtyChangedOnly($transacId, $toBeQtyChangedOnly);

        $this->assertEmpty($result['toBeCancelledBorrowedItemIds']);
        $this->assertEmpty($result['itemsToBeAdded']);
    }
}