<?php

namespace Tests\Feature\Controllers\ManageBorrowingRequestController;

use App\Http\Controllers\BorrowTransaction\ManageBorrowingRequestController;
use App\Models\BorrowedItem;
use App\Models\BorrowTransaction;
use App\Models\Department;
use App\Models\ItemGroup;
use App\Http\Requests\BorrowTransaction\SubmitBorrowRequestForMultipleOfficesRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class SubmitBorrowRequestV2Test extends TestCase
{
    use RefreshDatabase;

    protected $itroId;
    protected $esloId;
    protected $bmoId;
    protected $itemGroups;
    protected $controller;

    private function getSuccessResponse(int $transacCount)
    {
        return [
            "status" => true,
            "message" => "Successfully submitted " . $transacCount . " borrow request",
            "method" => "POST"
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->seed();

        // Create a user
        $user = User::factory()->create();
        // Log the user in
        $this->actingAs($user);

        $this->controller = app(ManageBorrowingRequestController::class);
        $this->itroId = Department::getIdBasedOnAcronym('ITRO');
        $this->esloId = Department::getIdBasedOnAcronym('ESLO');
        $this->bmoId = Department::getIdBasedOnAcronym('BMO');


        // Create necessary item groups
        $this->itemGroups = [
            'Canon 200d' => ItemGroup::getIdByModelNameAndOfficeId('Canon 200d', $this->itroId),
            'Arduino Uno R4 WiFi' => ItemGroup::getIdByModelNameAndOfficeId('Arduino Uno R4 WiFi', $this->itroId),
            'FLUKE T150 Voltage Tester' => ItemGroup::getIdByModelNameAndOfficeId('FLUKE T150 Voltage Tester', $this->esloId),
            'Micron Cresta ZS Microscope' => ItemGroup::getIdByModelNameAndOfficeId('Micron Cresta ZS Microscope', $this->esloId),
            'Lifetime 6ft Folding Table' => ItemGroup::getIdByModelNameAndOfficeId('Lifetime 6ft Folding Table', $this->bmoId),
            'Spalding FIBA 2007' => ItemGroup::getIdByModelNameAndOfficeId('Spalding FIBA 2007', $this->bmoId),
        ];
    }
    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_able_to_submit_request_for_three_offices()
    {
        $this->withoutMiddleware(); // Skips all middleware

        $requestData = [
            'purpose' => "OTHERS",
            'user_defined_purpose' => 'sfsfs',
            "items" => [
                // ITRO
                [
                    'item_group_id' => $this->itemGroups['Canon 200d'],
                    'quantity' => 3,
                    'start_date' => (string) now()->addDay(),
                    'return_date' => (string) now()->addWeek(),
                ],
                [
                    'item_group_id' => $this->itemGroups['Arduino Uno R4 WiFi'],
                    'quantity' => 3,
                    'start_date' => (string) now()->addDay(),
                    'return_date' => (string) now()->addWeek(),
                ],

                // ESLO
                [
                    'item_group_id' => $this->itemGroups['FLUKE T150 Voltage Tester'],
                    'quantity' => 3,
                    'start_date' => (string) now()->addDay(),
                    'return_date' => (string) now()->addWeek(),
                ],
                [
                    'item_group_id' => $this->itemGroups['Micron Cresta ZS Microscope'],
                    'quantity' => 3,
                    'start_date' => (string) now()->addDay(),
                    'return_date' => (string) now()->addWeek(),
                ],

                // BMO
                [
                    'item_group_id' => $this->itemGroups['Lifetime 6ft Folding Table'],
                    'quantity' => 3,
                    'start_date' => (string) now()->addDay(),
                    'return_date' => (string) now()->addWeek(),
                ],
                [
                    'item_group_id' => $this->itemGroups['Spalding FIBA 2007'],
                    'quantity' => 3,
                    'start_date' => (string) now()->addDay(),
                    'return_date' => (string) now()->addWeek(),
                ],
            ]
        ];

        $response = $this->postJson('api/user/borrow-request/submit-V2', $requestData);
        $borrowedItems = BorrowedItem::count();
        $transactionCount = BorrowTransaction::count();

        $this->assertEquals($borrowedItems, 18);
        $this->assertEquals($transactionCount, 3);
        $response->assertJsonStructure([
            'status',
            'message',
            'method'
        ]);
        $response->assertJson(self::getSuccessResponse(3));
    }

    public function test_able_to_submit_request_for_one_office()
    {
        $requestData = [
            'purpose' => "OTHERS",
            'user_defined_purpose' => 'sfsfs',
            "items" => [
                // ITRO
                [
                    'item_group_id' => $this->itemGroups['Canon 200d'],
                    'quantity' => 3,
                    'start_date' => (string) now()->addDay(),
                    'return_date' => (string) now()->addWeek(),
                ]
            ]
        ];

        $response = $this->postJson('api/user/borrow-request/submit-V2', $requestData);
        $borrowedItems = BorrowedItem::count();
        $transactionCount = BorrowTransaction::count();

        $this->assertEquals($borrowedItems, 3);
        $this->assertEquals($transactionCount, 1);
        $response->assertJsonStructure([
            'status',
            'message',
            'method'
        ]);
        $response->assertJson(self::getSuccessResponse(1));
    }

    // The quantity registered will be from the last entry 
    // In this case, only 1 item will be borrowed
    public function test_two_duplicate_item_group_results_to_one_transaction()
    {
        $requestData = [
            'purpose' => "OTHERS",
            'user_defined_purpose' => 'sfsfs',
            "items" => [
                // ITRO
                [
                    'item_group_id' => $this->itemGroups['Canon 200d'],
                    'quantity' => 2,
                    'start_date' => (string) now()->addDay(),
                    'return_date' => (string) now()->addWeek(),
                ],
                [
                    'item_group_id' => $this->itemGroups['Canon 200d'],
                    'quantity' => 1,
                    'start_date' => (string) now()->addDay(),
                    'return_date' => (string) now()->addWeek(),
                ]
            ]
        ];

        $response = $this->postJson('api/user/borrow-request/submit-V2', $requestData);
        $borrowedItems = BorrowedItem::count();
        $transactionCount = BorrowTransaction::count();

        $this->assertEquals($borrowedItems, 1);
        $this->assertEquals($transactionCount, 1);
        $response->assertJsonStructure([
            'status',
            'message',
            'method'
        ]);
        $response->assertJson(self::getSuccessResponse(1));
    }
}