<?php

namespace Tests\Unit\Services\BorrowRequestService\EditBorrowRequestService;

use App\Services\BorrowRequestService\EditBorrowRequestService;
use Tests\TestCase;

class SegregateToBeEditedItemsTest extends TestCase
{
    private $service;
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EditBorrowRequestService();
    }
    /**
     * Test when start_date, return_date, and quantity all exist.
     */
    public function test_all_fields_exist()
    {
        $toBeEdited = [
            [
                'item_group_id' => 1,
                'start_date' => '2024-10-10',
                'return_date' => '2024-10-15',
                'quantity' => 5,
            ],
            [
                'item_group_id' => 2,
                'start_date' => '2025-10-10',
                'return_date' => '2025-10-15',
                'quantity' => 2,
            ],
            [
                'item_group_id' => 3,
                'start_date' => '2025-10-10',
                'return_date' => '2025-10-15',
                'quantity' => 1,
            ],
        ];

        $result = $this->service->segregateToBeEditedItems($toBeEdited);

        $this->assertCount(3, $result['toBeEdited']);
        $this->assertEmpty($result['toBeCancelledIds']);
        $this->assertEmpty($result['toBeQtyChangedOnly']);
    }

    /**
     * Test when only start_date and return_date exist.
     */
    public function test_only_dates_exist()
    {
        $toBeEdited = [
            [
                'item_group_id' => 1,
                'start_date' => '2024-10-12',
                'return_date' => '2024-10-20',
            ],
            [
                'item_group_id' => 2,
                'start_date' => '2025-10-12',
                'return_date' => '2025-10-20',
            ],
        ];

        $result = $this->service->segregateToBeEditedItems($toBeEdited);

        $this->assertCount(2, $result['toBeEdited']);
        $this->assertEmpty($result['toBeCancelledIds']);
        $this->assertEmpty($result['toBeQtyChangedOnly']);
    }

    /**
     * Test when only is_cancelled exists.
     */
    public function test_only_cancelled_exists()
    {
        $toBeEdited = [
            [
                'item_group_id' => 1,
                'is_cancelled' => true,
            ],
            [
                'item_group_id' => 2,
                'is_cancelled' => true,
            ],
            [
                'item_group_id' => 3,
                'is_cancelled' => true,
            ],
        ];

        $result = $this->service->segregateToBeEditedItems($toBeEdited);

        $this->assertEmpty($result['toBeEdited']);
        $this->assertContains(3, $result['toBeCancelledIds']);
        $this->assertEmpty($result['toBeQtyChangedOnly']);
    }

    /**
     * Test when only quantity exists.
     */
    public function test_only_quantity_exists()
    {
        $toBeEdited = [
            [
                'item_group_id' => 1,
                'quantity' => 2,
            ],
            [
                'item_group_id' => 2,
                'quantity' => 5,
            ],
            [
                'item_group_id' => 3,
                'quantity' => 6,
            ],
        ];

        $result = $this->service->segregateToBeEditedItems($toBeEdited);

        $this->assertEmpty($result['toBeEdited']);
        $this->assertEmpty($result['toBeCancelledIds']);
        $this->assertCount(3, $result['toBeQtyChangedOnly']);
    }

    /**
     * Test when all fields and only date fields exists.
     */
    public function test_all_fields_and_only_dates_exists()
    {
        $toBeEdited = [
            [
                'item_group_id' => 1,
                'start_date' => '2024-10-10',
                'return_date' => '2024-10-15',
                'quantity' => 5,
            ],
            [
                'item_group_id' => 2,
                'start_date' => '2025-10-10',
                'return_date' => '2025-10-15',
                'quantity' => 2,
            ],
            [
                'item_group_id' => 3,
                'start_date' => '2025-10-10',
                'return_date' => '2025-10-15',
                'quantity' => 1,
            ],
            [
                'item_group_id' => 4,
                'start_date' => '2025-10-10',
                'return_date' => '2025-10-15',
            ],
            [
                'item_group_id' => 5,
                'start_date' => '2025-10-10',
                'return_date' => '2025-10-15',
            ],
        ];

        $result = $this->service->segregateToBeEditedItems($toBeEdited);

        $this->assertCount(5, $result['toBeEdited']);
        $this->assertEmpty($result['toBeCancelledIds']);
        $this->assertEmpty($result['toBeQtyChangedOnly']);
    }


    /**
     * Test when all fields and iscancelled fields exists.
     */
    public function test_all_fields_and_is_cancelled_exists()
    {
        $toBeEdited = [
            [
                'item_group_id' => 1,
                'start_date' => '2024-10-10',
                'return_date' => '2024-10-15',
                'quantity' => 5,
            ],
            [
                'item_group_id' => 2,
                'start_date' => '2025-10-10',
                'return_date' => '2025-10-15',
                'quantity' => 2,
            ],
            [
                'item_group_id' => 1,
                'is_cancelled' => true,
            ],
            [
                'item_group_id' => 2,
                'is_cancelled' => true,
            ],
            [
                'item_group_id' => 3,
                'is_cancelled' => true,
            ],
            [
                'item_group_id' => 3,
                'start_date' => '2025-10-10',
                'return_date' => '2025-10-15',
                'quantity' => 1,
            ],
        ];

        $result = $this->service->segregateToBeEditedItems($toBeEdited);

        $this->assertCount(3, $result['toBeEdited']);
        $this->assertCount(3, $result['toBeCancelledIds']);
        $this->assertEmpty($result['toBeQtyChangedOnly']);
    }

    /**
     * Test when only date fields and iscancelled fields exists.
     */
    public function test_only_date_fields_and_is_cancelled_exists()
    {
        $toBeEdited = [
            [
                'item_group_id' => 1,
                'start_date' => '2024-10-10',
                'return_date' => '2024-10-15',
            ],
            [
                'item_group_id' => 2,
                'start_date' => '2025-10-10',
                'return_date' => '2025-10-15',
            ],
            [
                'item_group_id' => 1,
                'is_cancelled' => true,
            ],
            [
                'item_group_id' => 2,
                'is_cancelled' => true,
            ],
            [
                'item_group_id' => 3,
                'is_cancelled' => true,
            ],
            [
                'item_group_id' => 3,
                'start_date' => '2025-10-10',
                'return_date' => '2025-10-15',
            ],
        ];

        $result = $this->service->segregateToBeEditedItems($toBeEdited);

        $this->assertCount(3, $result['toBeEdited']);
        $this->assertCount(3, $result['toBeCancelledIds']);
        $this->assertEmpty($result['toBeQtyChangedOnly']);
    }

    /**
     * Test when mixed exists.
     */
    public function test_mixed_fields_exists()
    {
        $toBeEdited = [
            [
                'item_group_id' => 1,
                'is_cancelled' => true,
            ],
            [
                'item_group_id' => 1,
                'start_date' => '2024-10-10',
                'return_date' => '2024-10-15',
                'quantity' => 5,
            ],
            [
                'item_group_id' => 3,
                'start_date' => '2025-10-10',
                'return_date' => '2025-10-15',
            ],
            [
                'item_group_id' => 2,
                'start_date' => '2025-10-10',
                'return_date' => '2025-10-15',
                'quantity' => 2,
            ],
            [
                'item_group_id' => 1,
                'quantity' => 2,
            ],
            [
                'item_group_id' => 2,
                'quantity' => 5,
            ],
            [
                'item_group_id' => 1,
                'is_cancelled' => true,
            ],
            [
                'item_group_id' => 2,
                'is_cancelled' => true,
            ],
            [
                'item_group_id' => 3,
                'is_cancelled' => true,
            ],
            [
                'item_group_id' => 3,
                'start_date' => '2025-10-10',
                'return_date' => '2025-10-15',
                'quantity' => 1,
            ],
            [
                'item_group_id' => 3,
                'quantity' => 6,
            ],
        ];

        $result = $this->service->segregateToBeEditedItems($toBeEdited);

        $this->assertCount(4, $result['toBeEdited']);
        $this->assertCount(4, $result['toBeCancelledIds']);
        $this->assertCount(3, $result['toBeQtyChangedOnly']);
    }


}