<?php

namespace App\Http\Controllers\BorrowTransaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\BorrowTransaction\GetBorrowTransactionRequest;
use App\Http\Resources\BorrowTransactionResource;
use App\Models\BorrowedItem;
use App\Models\BorrowTransaction;
use App\Models\Department;
use App\Utils\Constants\OfficeCodes;
use Illuminate\Http\Request;

class ManageBorrowTransactionController extends Controller
{
    protected $user;
    protected $lendingOfficesArray;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = auth()->user();
            return $next($request);
        });

        $this->lendingOfficesArray = OfficeCodes::LENDING_OFFICES_ARRAY;
    }
    /**
     * Display transaction list
     */
    public function index()
    {
        // Check from which office user belongs
        $userDepartmentId = $this->user->department_id;

        $userDepartmentCode = Department::where('id', $userDepartmentId)->firstOrFail()->department_code;

        if (!in_array($userDepartmentCode, $this->lendingOfficesArray)) {
            return response([
                'status' => false,
                'message' => "You are not a lending employee",
                'method' => "GET"
            ]);
        }

        $transactions = BorrowTransaction::where('department_id', $userDepartmentId)->get()->toArray();

        return response([
            'status' => true,
            'data' => $transactions,
            'method' => "GET"
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Get specific Borrow request
     */
    public function show(GetBorrowTransactionRequest $request)
    {
        try {
            $request = $request->validated();
            $transacId = $request['transactionId'];

            $transacData = BorrowTransaction::find($transacId);

            // $items = BorrowedItem::where('borrowing_transac_id', $transacId)->get();
            $items = BorrowedItem::where('borrowing_transac_id', $transacId)
                ->join('items', 'borrowed_items.item_id', '=', 'items.id')
                ->join('item_groups', 'items.item_group_id', '=', 'item_groups.id')
                ->join(
                    'borrowed_item_statuses',
                    'borrowed_items.borrowed_item_status_id',
                    '=',
                    'borrowed_item_statuses.id'
                )
                ->groupBy(
                    'item_groups.model_name',
                    'item_groups.id',
                    'borrowed_items.start_date',
                    'borrowed_items.due_date',
                    'borrowed_items.borrowed_item_status_id',

                )
                ->select(
                    'item_groups.model_name',
                    'item_groups.id',
                    \DB::raw('COUNT(borrowed_items.id) as quantity'),
                    'borrowed_items.start_date',
                    'borrowed_items.due_date',
                    'borrowed_item_statuses.borrowed_item_status'
                )
                ->get();


            // Restructured due to the table in react needing the item field haha
            $restructuredItems = $items
                ->map(function ($item) {
                    return [
                        'item' => [
                            'model_name' => $item->model_name,
                            // 'id' => $item->id,
                        ],
                        'quantity' => $item->quantity,
                        'start_date' => $item->start_date,
                        'due_date' => $item->due_date,
                        'borrowed_item_status' => $item->borrowed_item_status,
                    ];

                });
            return response([
                'status' => true,
                'data' => [
                    'transac_data' => new BorrowTransactionResource($transacData),
                    'items' => $restructuredItems,
                ],
                'method' => "GET"
            ], 200);
        } catch (\Exception $e) {
            return response([
                'status' => false,
                'message' => "An error occured, try again later",
                'error' => $e,
                'method' => "GET"
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
