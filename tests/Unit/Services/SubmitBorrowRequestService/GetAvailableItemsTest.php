<?php

namespace Tests\Unit\Services\SubmitBorrowRequestService;

use App\Services\BorrowRequestService\SubmitBorrowRequestService;
use App\Services\ItemAvailability;
use Tests\TestCase;

class GetAvailableItemsTest extends TestCase
{
    private $service;
    private $mockItemAvailability;

    protected function setUp(): void
    {
        parent::setUp();

        // Mocking the itemAvailability service
        $this->mockItemAvailability = $this->createMock(ItemAvailability::class);

        // Initializing the service with the mocked dependency
        $this->service = new SubmitBorrowRequestService($this->mockItemAvailability);
    }

    /** @test */
    public function test_removes_unavailable_items_from_the_list()
    {
        // Define input data
        $activeItems = [
            'group1' => [
                'item_id' => ['item1', 'item2'],
                'start_date' => '2024-09-01',
                'return_date' => '2024-09-05',
            ],
            'group2' => [
                'item_id' => ['item3'],
                'start_date' => '2024-09-02',
                'return_date' => '2024-09-06',
            ],
        ];

        // Mocking the isAvailable method responses
        $this->mockItemAvailability
            ->expects($this->exactly(3)) // Expect 3 checks based on the input data
            ->method('isAvailable')
            ->willReturnMap([
                ['item1', '2024-09-01', '2024-09-05', true],  // Available
                ['item2', '2024-09-01', '2024-09-05', false], // Not available
                ['item3', '2024-09-02', '2024-09-06', true],  // Available
            ]);

        // Call the method
        $result = $this->service->getAvailableItems($activeItems);

        // Expected output
        $expected = [
            'group1' => [
                'item_id' => ['item1'], // item2 is removed because it is not available
                'start_date' => '2024-09-01',
                'return_date' => '2024-09-05',
            ],
            'group2' => [
                'item_id' => ['item3'],
                'start_date' => '2024-09-02',
                'return_date' => '2024-09-06',
            ],
        ];

        // Assert the result matches the expected output
        $this->assertEquals($expected, $result);
    }

    /** @test */
    public function test_returns_available_items_with_empty_item_id_array()
    {
        // Define input data with an empty item_id array
        $activeItems = [
            'ITEM_GROUP_ID_01' => [
                'item_id' => [], // Empty item_id array
                'start_date' => '2024-09-27 15:00:00',
                'return_date' => '2024-09-28 16:35:00',
                'quantity' => 2
            ],
        ];

        // Call the method
        $result = $this->service->getAvailableItems($activeItems);

        // Expected output should be the same as input as there are no item_ids to process
        $expected = [
            'ITEM_GROUP_ID_01' => [
                'item_id' => [], // Still an empty array
                'start_date' => '2024-09-27 15:00:00',
                'return_date' => '2024-09-28 16:35:00',
                'quantity' => 2
            ],
        ];

        // Assert the result matches the expected output
        $this->assertEquals($expected, $result);
    }
}
