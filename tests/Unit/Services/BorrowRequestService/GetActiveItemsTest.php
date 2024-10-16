<?php

namespace Tests\Unit\Services\BorrowRequestService;

use ReflectionClass;
use Tests\TestCase;
use Mockery as m;
use App\Models\Item;
use App\Services\BorrowRequestService\BorrowRequestHelperService;

class GetActiveItemsTest extends TestCase
{
    private $service;
    private $itemMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Initialize the service instance
        $this->service = app(BorrowRequestHelperService::class);

        // Use Reflection to set the protected/private property
        $reflection = new ReflectionClass($this->service);
        $property = $reflection->getProperty('activeItemStatusId');
        $property->setAccessible(true);
        $property->setValue($this->service, 'ACTIVE');

        // Arrange: Mock the Item model
        $this->itemMock = m::mock('alias:' . Item::class);
    }
    /** @test */
    public function test_get_active_items_for_one_requested_item_group()
    {
        // Sample request data
        $requestedItems = [
            [
                'item_group_id' => 'ITEM_GROUP_ID_01',
                'start_date' => '2024-09-26 15:00:00',
                'return_date' => '2024-09-30 16:35:00',
                'quantity' => 2,
            ],
        ];

        // Expected item IDs to be returned by the mocked query
        $expectedItemIds = [
            'item_id_01',
            'item_id_02',
            'item_id_03',
            'item_id_04',
        ];

        // Mock the query to return the expected item IDs
        $this->itemMock->shouldReceive('where')
            ->with('item_group_id', 'ITEM_GROUP_ID_01')
            ->andReturnSelf();

        $this->itemMock->shouldReceive('where')
            ->with('item_status_id', 'ACTIVE') // Assuming 1 is the active status ID
            ->andReturnSelf();

        $this->itemMock->shouldReceive('pluck')
            ->with('id')
            ->andReturn(collect($expectedItemIds));

        // Act: Call the method
        $activeItems = $this->service->getActiveItems($requestedItems);

        // Assert: Check if the method returns the expected structure
        $expectedResult = [
            'ITEM_GROUP_ID_01' => [
                'item_id' => collect($expectedItemIds),
                'start_date' => '2024-09-26 15:00:00',
                'return_date' => '2024-09-30 16:35:00',
                'quantity' => 2,
            ],
        ];

        $this->assertEquals($expectedResult, $activeItems);
    }

    // Get Active Items for 2 Requested Item Model
    public function test_get_active_items_for_two_requested_item_group()
    {
        // Prepare
        // Sample request data
        $requestedItems = [
            [
                'item_group_id' => 'ITEM_GROUP_ID_01',
                'start_date' => '2024-09-26 15:00:00',
                'return_date' => '2024-09-30 16:35:00',
                'quantity' => 2,
            ],
            [
                'item_group_id' => 'ITEM_GROUP_ID_02',
                'start_date' => '2024-09-26 15:00:00',
                'return_date' => '2024-09-30 16:35:00',
                'quantity' => 2,
            ],
        ];

        // Expected item IDs to be returned by the mocked query
        $expectedItemIds_01 = [
            'item_id_01',
            'item_id_02',
            'item_id_03',
            'item_id_04',
        ];
        $expectedItemIds_02 = [
            'item_id_05',
            'item_id_06',
            'item_id_07',
            'item_id_08',
        ];

        // 01 Mock the Query on the first loop
        $this->itemMock->shouldReceive('where')
            ->with('item_group_id', 'ITEM_GROUP_ID_01')
            ->andReturnSelf();

        $this->itemMock->shouldReceive('where')
            ->with('item_status_id', 'ACTIVE') // Assuming 1 is the active status ID
            ->andReturnSelf();

        $this->itemMock->shouldReceive('pluck')
            ->with('id')
            ->andReturn(collect($expectedItemIds_01));


        // 02 Mock the Query on the second loop
        $this->itemMock->shouldReceive('where')
            ->with('item_group_id', 'ITEM_GROUP_ID_02')
            ->andReturnSelf();

        $this->itemMock->shouldReceive('where')
            ->with('item_status_id', 'ACTIVE') // Assuming 1 is the active status ID
            ->andReturnSelf();

        $this->itemMock->shouldReceive('pluck')
            ->with('id')
            ->andReturn(collect($expectedItemIds_02));

        // Execute
        $activeItems = $this->service->getActiveItems($requestedItems);

        // Assertions based on the mocked behavior
        $this->assertNotEmpty($activeItems);
        $this->assertArrayHasKey('ITEM_GROUP_ID_01', $activeItems);
        $this->assertArrayHasKey('ITEM_GROUP_ID_02', $activeItems);
    }


    // Test No Active Items
    public function test_no_active_items()
    {
        // Sample request data
        $requestedItems = [
            [
                'item_group_id' => 'ITEM_GROUP_ID_01',
                'start_date' => '2024-09-26 15:00:00',
                'return_date' => '2024-09-30 16:35:00',
                'quantity' => 2,
            ],
        ];

        // No active items returned
        $expectedItemIds = [];

        // Mock the query to return the expected item IDs
        $this->itemMock->shouldReceive('where')
            ->with('item_group_id', 'ITEM_GROUP_ID_01')
            ->andReturnSelf();

        $this->itemMock->shouldReceive('where')
            ->with('item_status_id', 'ACTIVE') // Assuming 1 is the active status ID
            ->andReturnSelf();

        $this->itemMock->shouldReceive('pluck')
            ->with('id')
            ->andReturn(collect($expectedItemIds));

        // Act: Call the method
        $activeItems = $this->service->getActiveItems($requestedItems);

        // Assert: Check if the method returns the expected structure
        $expectedResult = [
            'ITEM_GROUP_ID_01' => [
                'item_id' => collect($expectedItemIds),
                'start_date' => '2024-09-26 15:00:00',
                'return_date' => '2024-09-30 16:35:00',
                'quantity' => 2,
            ],
        ];

        $this->assertEquals($expectedResult, $activeItems);
    }
    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }
}
