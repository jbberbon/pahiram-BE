<?php

namespace Tests\Unit\Services\BorrowRequestService;

use App\Services\BorrowRequestService\BorrowRequestHelperService;
use Tests\TestCase;
use Mockery as m;
use Illuminate\Http\JsonResponse;
use App\Models\BorrowTransaction;

class CheckMaxTransactionsTest extends TestCase
{
    private $service;
    private $maxTransactionCount;
    protected function setUp(): void
    {
        parent::setUp();
        $this->maxTransactionCount = 4;

        // Initialize the service instance
        $this->service = app(BorrowRequestHelperService::class);
    }
    /** @test */
    public function test_check_max_transactions_exceeds_limit()
    {
        $userId = 1; // Example user ID

        // Mock the BorrowTransaction model and its methods
        $borrowTransactionMock = m::mock('alias:' . BorrowTransaction::class);

        // Mock the query and return 3 transactions
        $borrowTransactionMock->shouldReceive('where')
            ->with('borrower_id', $userId)
            ->andReturnSelf();

        $borrowTransactionMock->shouldReceive('whereIn')
            ->with('transac_status_id', m::any())
            ->andReturnSelf();

        $borrowTransactionMock->shouldReceive('count')
            ->andReturn($this->maxTransactionCount);

        // Call the method
        $response = $this->service->checkMaxTransactions($userId);

        // Assert the response contains the expected message
        $this->assertIsArray($response);
        $this->assertArrayHasKey('status', $response);
        $this->assertFalse($response['status']);
        $this->assertEquals('Complete your other 3 or more transactions first.', $response['message']);
        $this->assertEquals('GET', $response['method']);

    }
    /** @test */
    public function test_check_max_transactions_below_limit()
    {
        $userId = 1; // Example user ID

        // Mock the BorrowTransaction model and its methods
        $borrowTransactionMock = m::mock('alias:' . BorrowTransaction::class);

        // Mock the query and return 3 transactions
        $borrowTransactionMock->shouldReceive('where')
            ->with('borrower_id', $userId)
            ->andReturnSelf();

        $borrowTransactionMock->shouldReceive('whereIn')
            ->with('transac_status_id', m::any())
            ->andReturnSelf();

        $borrowTransactionMock->shouldReceive('count')
            ->andReturn($this->maxTransactionCount - 1);

        // Call the method
        $response = $this->service->checkMaxTransactions($userId);

        // Assert the response contains the expected message
        $this->assertNull($response);
    }

    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }
}
