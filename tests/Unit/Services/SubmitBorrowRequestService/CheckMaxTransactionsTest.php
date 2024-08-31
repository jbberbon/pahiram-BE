<?php

namespace Tests\Unit\Services\SubmitBorrowRequestService;

use App\Services\BorrowRequestService\SubmitBorrowRequestService;
use Tests\TestCase;
use Mockery as m;
use Illuminate\Http\JsonResponse;
use App\Models\BorrowTransaction;

class CheckMaxTransactionsTest extends TestCase
{
    private $service;
    protected function setUp(): void
    {
        parent::setUp();

        // Initialize the service instance
        $this->service = app(SubmitBorrowRequestService::class);
    }
    /** @test */
    public function test_check_max_transactions_exceeds_limit()
    {
        // Arrange: Mock the BorrowTransaction model
        $borrowTransactionMock = m::mock('alias:' . BorrowTransaction::class);

        $userId = 1;

        // Set up the mock to return a count greater than or equal to MAX_ACTIVE_TRANSACTIONS
        $borrowTransactionMock->shouldReceive('where')
            ->with('borrower_id', $userId)
            ->andReturnSelf();

        $borrowTransactionMock->shouldReceive('whereIn')
            ->with('transac_status_id', m::type('array'))
            ->andReturnSelf();

        $borrowTransactionMock->shouldReceive('count')
            ->andReturn(3); // Assuming the limit is 3, adjust accordingly

        // Act: Call the method
        $response = $this->service->checkMaxTransactions($userId);

        // Assert: Check if the method returns the expected JsonResponse
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(401, $response->getStatusCode());

        $responseData = $response->getData(true);
        $this->assertFalse($responseData['status']);
        $this->assertEquals('Complete your other 3 or more transactions first.', $responseData['message']);
        $this->assertEquals('GET', $responseData['method']);
    }
    /** @test */
    public function test_check_max_transactions_below_limit()
    {
        // Arrange: Mock the BorrowTransaction model
        $borrowTransactionMock = m::mock('alias:' . BorrowTransaction::class);

        $userId = 1;

        // Set up the mock to return a count less than MAX_ACTIVE_TRANSACTIONS
        $borrowTransactionMock->shouldReceive('where')
            ->with('borrower_id', $userId)
            ->andReturnSelf();

        $borrowTransactionMock->shouldReceive('whereIn')
            ->with('transac_status_id', m::type('array'))
            ->andReturnSelf();

        $borrowTransactionMock->shouldReceive('count')
            ->andReturn(2); // Below the limit

        // Act: Call the method
        $response = $this->service->checkMaxTransactions($userId);

        // Assert: Check if the method returns null
        $this->assertNull($response);
    }

    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }
}
