<?php

namespace Tests\Feature\Service\ItemAvailability;

use App\Models\Item;
use App\Services\ItemAvailability;

use App\Utils\Constants\Statuses\BORROWED_ITEM_STATUS;
use Database\Seeders\Testing\Services\ItemAvailabilitySeeder;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IsAvailableTest extends TestCase
{
    use RefreshDatabase;
    protected $baseDates = [
        'borrowed_item_status' => BORROWED_ITEM_STATUS::IN_POSSESSION,
        'start_date' => '2024-11-01 12:00:00',
        'due_date' => '2024-11-02 12:00:00'
    ];

    protected function setUp(): void
    {
        parent::setUp();
    }
    protected function tearDown(): void
    {
        parent::tearDown();
    }


    public function test_no_overlap()
    {
        // Prepare
        $seeder = new ItemAvailabilitySeeder();
        $seeder->run([
            'borrowed_item_status' => BORROWED_ITEM_STATUS::RETURNED,
            ...$this->baseDates
        ]);

        $service = new ItemAvailability();
        $borrowedItem = Item::first();

        // Execute
        $result = $service->isAvailable(
            $borrowedItem->id,
            '2024-11-02 12:00:00',
            '2024-11-03 12:00:00'
        );

        // Assert
        $this->assertTrue($result);
    }

    public function test_exact_overlap()
    {
        // Prepare
        $seeder = new ItemAvailabilitySeeder();
        $seeder->run([
            'borrowed_item_status' => BORROWED_ITEM_STATUS::IN_POSSESSION,
            ...$this->baseDates
        ]);

        $service = new ItemAvailability();
        $borrowedItem = Item::first();

        // Execute
        $result = $service->isAvailable(
            $borrowedItem->id,
            '2024-11-01 12:00:00',
            '2024-11-02 12:00:00'
        );

        // Assert
        $this->assertFalse($result);
    }

    public function test_one_minute_overlap_at_the_end()
    {
        // Prepare
        $seeder = new ItemAvailabilitySeeder();
        $seeder->run([
            'borrowed_item_status' => BORROWED_ITEM_STATUS::IN_POSSESSION,
            ...$this->baseDates
        ]);

        $service = new ItemAvailability();
        $borrowedItem = Item::first();

        // Execute
        $result = $service->isAvailable(
            $borrowedItem->id,
            '2024-11-02 11:59:00',
            '2024-11-03 12:00:00'
        );

        // Assert
        $this->assertFalse($result);
    }

    public function test_one_minute_overlap_at_the_beginning()
    {
        // Prepare
        $seeder = new ItemAvailabilitySeeder();
        $seeder->run([
            'borrowed_item_status' => BORROWED_ITEM_STATUS::IN_POSSESSION,
            ...$this->baseDates
        ]);

        $service = new ItemAvailability();
        $borrowedItem = Item::first();

        // Execute
        $result = $service->isAvailable(
            $borrowedItem->id,
            '2024-10-31 11:59:00',
            '2024-11-01 12:01:00'
        );

        // Assert
        $this->assertFalse($result);
    }

    public function test_partial_overlap()
    {
        // Prepare
        $seeder = new ItemAvailabilitySeeder();
        $seeder->run([
            'borrowed_item_status' => BORROWED_ITEM_STATUS::APPROVED,
            ...$this->baseDates
        ]);

        $service = new ItemAvailability();
        $borrowedItem = Item::first();

        // Execute
        $result = $service->isAvailable(
            $borrowedItem->id,
            '2024-11-02 08:00:00',
            '2024-11-03 12:00:00'
        );

        // Assert
        $this->assertFalse($result);
    }

    public function test_no_record_or_history_of_the_item_being_borrowed()
    {
        // Prepare
        $service = new ItemAvailability();

        // Execute
        $result = $service->isAvailable(
            'TEST ID',
            '2024-11-02 08:00:00',
            '2024-11-03 12:00:00'
        );

        // Assert
        $this->assertTrue($result);
    }

    public function test_date_overlap_for_overdue_return_item()
    {
        // Prepare
        $seeder = new ItemAvailabilitySeeder();
        $seeder->run([
            'borrowed_item_status' => BORROWED_ITEM_STATUS::IN_POSSESSION,
            'start_date' => '2024-04-02 14:00:00',
            'due_date' => '2024-04-03 12:00:00'
        ]);

        $service = new ItemAvailability();
        $borrowedItem = Item::first();

        // Execute
        $result = $service->isAvailable(
            $borrowedItem->id,
            '2024-11-02 14:00:00',
            '2024-11-03 12:00:00'
        );

        // Assert
        $this->assertFalse($result);
    }
}