<?php

namespace Tests\Feature\Service\ItemAvailability;

use App\Models\BorrowedItem;
use App\Models\BorrowedItemStatus;
use App\Services\ItemAvailability;
use App\Utils\Constants\Statuses\BORROWED_ITEM_STATUS;
use App\Utils\DateUtil;
use Database\Seeders\Testing\Services\ItemAvailabilitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IsBorrowedItemOverdueTest extends TestCase
{
    use RefreshDatabase;

    protected $inPossessionStatusId;

    protected function setUp(): void
    {
        parent::setUp();

    }

    public function test_borrowed_item_with_approved_status_is_not_overdue()
    {
        // Prepare
        $seeder = new ItemAvailabilitySeeder();
        $seeder->run([
            'borrowed_item_status' => BORROWED_ITEM_STATUS::APPROVED,
            'start_date' => '2024-04-02 14:00:00',
            'due_date' => now()->subDay()
        ]);

        $service = new ItemAvailability();
        $borrowedItem = BorrowedItem::first();

        // Execute
        $isOverdue = $service->isBorrowedItemOverdue($borrowedItem->toArray());

        // Assert
        $this->assertFalse($isOverdue);
    }

    public function test_borrowed_item_with_in_possession_status_is_not_overdue()
    {
        // Prepare
        $seeder = new ItemAvailabilitySeeder();
        $seeder->run([
            'borrowed_item_status' => BORROWED_ITEM_STATUS::IN_POSSESSION,
            'start_date' => '2024-04-02 14:00:00',
            'due_date' => now()->addDay()
        ]);

        $service = new ItemAvailability();
        $borrowedItem = BorrowedItem::first();

        // Execute
        $isOverdue = $service->isBorrowedItemOverdue($borrowedItem->toArray());

        // Assert
        $this->assertFalse($isOverdue);
    }

    public function test_borrowed_item_is_one_day_overdue()
    {
        // Prepare
        $seeder = new ItemAvailabilitySeeder();
        $seeder->run([
            'borrowed_item_status' => BORROWED_ITEM_STATUS::IN_POSSESSION,
            'start_date' => '2024-04-02 14:00:00',
            'due_date' => now()->subDay()
        ]);

        $service = new ItemAvailability();
        $borrowedItem = BorrowedItem::first();

        // Execute
        $isOverdue = $service->isBorrowedItemOverdue($borrowedItem->toArray());

        // Assert
        $this->assertTrue($isOverdue);
    }

    public function test_borrowed_item_is_week_overdue()
    {
        // Prepare
        $seeder = new ItemAvailabilitySeeder();
        $seeder->run([
            'borrowed_item_status' => BORROWED_ITEM_STATUS::IN_POSSESSION,
            'start_date' => '2024-04-02 14:00:00',
            'due_date' => now()->subWeek()
        ]);

        $service = new ItemAvailability();
        $borrowedItem = BorrowedItem::first();

        // Execute
        $isOverdue = $service->isBorrowedItemOverdue($borrowedItem->toArray());

        // Assert
        $this->assertTrue($isOverdue);
    }

}
