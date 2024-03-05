<?php

namespace App\Http\Controllers\Penalty;

use App\Http\Controllers\Controller;
use App\Http\Requests\ManagePenalty\GetPenalizedTransactionListRequest;
use App\Http\Requests\ManagePenalty\PayPenaltyRequest;
use App\Http\Resources\BorrowTransactionResource;
use App\Http\Resources\ManagePenalty\PenalizedTransactionCollection;
use App\Models\BorrowedItem;
use App\Models\BorrowTransaction;
use App\Models\PenalizedTransaction;
use App\Models\PenalizedTransactionStatuses;
use App\Utils\Constants\Statuses\PENALIZED_TRANSAC_STATUS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;




class ManagePenaltyController extends Controller
{
    private $paidPenaltyStatusId;

    private $pendingPaymentStatusId;
    private $unpaidStatusId;

    public function __construct()
    {
        $this->paidPenaltyStatusId = PenalizedTransactionStatuses::getIdByStatus(PENALIZED_TRANSAC_STATUS::PAID);

        $this->pendingPaymentStatusId = PenalizedTransactionStatuses::getIdByStatus(PENALIZED_TRANSAC_STATUS::PENDING_PAYMENT);
        $this->unpaidStatusId = PenalizedTransactionStatuses::getIdByStatus(PENALIZED_TRANSAC_STATUS::UNPAID);

    }
    public function index(GetPenalizedTransactionListRequest $request)
    {
        $validatedData = $request->validated();
        try {
            if ($request->has('status')) {
                if ($validatedData['status'] === "PENDING_PAYMENT") {
                    $transactions = PenalizedTransaction::whereIn('status_id', [
                        $this->pendingPaymentStatusId,
                        $this->unpaidStatusId
                    ])->get();

                    return response([
                        'status' => true,
                        'data' => new PenalizedTransactionCollection($transactions),
                        'method' => "GET"
                    ], 200);

                }

                $statusId = PenalizedTransactionStatuses::getIdByStatus($validatedData['status']);
                $transactions = PenalizedTransaction::where('status_id', $statusId)->get();
                return response([
                    'status' => true,
                    'data' => new PenalizedTransactionCollection($transactions),
                    'method' => "GET"
                ], 200);
            }

            // No filter -> get all
            $transactions = PenalizedTransaction::all();
            return response([
                'status' => true,
                'data' => new PenalizedTransactionCollection($transactions),
                'method' => "GET"
            ], 200);
        } catch (\Exception $e) {
            return response([
                'status' => false,
                'message' => 'An error occurred while fetching transactions',
                'error' => $e->getMessage(),
                'method' => 'GET',
            ], 500);
        }
    }

    public function show($penalizedTransactionId)
    {
        try {
            $penalizedTransaction = PenalizedTransaction::findOrFail($penalizedTransactionId);

            $transaction = BorrowTransaction::findOrFail($penalizedTransaction->borrowing_transac_id);
            $items = BorrowedItem::where('borrowing_transac_id', $transaction->id)
                ->where('penalty', '>', 0)
                ->join('borrowed_item_statuses', 'borrowed_item_statuses.id', 'borrowed_items.borrowed_item_status_id')
                ->join('items', 'items.id', 'borrowed_items.item_id')
                ->join('item_groups', 'item_groups.id', 'items.item_group_id')

                ->select(
                    // 'borrowed_items.id as borrowed_item_id',
                    'borrowed_items.start_date',
                    'borrowed_items.due_date',
                    'borrowed_items.date_returned',
                    'borrowed_items.penalty',
                    'item_groups.model_name',
                    'borrowed_item_statuses.borrowed_item_status as status',
                    'items.apc_item_id',
                    // 'borrowed_items.status'
                )
                ->get();

            return response([
                'status' => true,
                'data' => [
                    'transac_data' => new BorrowTransactionResource($transaction),
                    'items' => $items
                ],
                'method' => "GET"
            ], 200);

        } catch (\Exception $e) {
            return response([
                'status' => false,
                'message' => 'An error occurred while fetching data',
                'error' => $e->getMessage(),
                'method' => 'GET',
            ], 500);
        }
    }

    public function payPenalty(PayPenaltyRequest $request)
    {
        try {
            DB::beginTransaction();
            $validatedData = $request->validated();

            $penalizedTransaction = PenalizedTransaction::find($validatedData['penalizedTransactionId']);
            $penalizedTransaction->update([
                'status_id' => $this->paidPenaltyStatusId,
                'receipt_number' => $validatedData['receipt_number'],
                'payment_facilitated_by' => Auth::user()->id,
                'paid_at' => now()->format('Y-m-d H:i:s'),
                'remarks_by_cashier' => $validatedData['remarks'] ?? null,

                // 'remarks_by_cashier' => isset($validatedData['remarks']) ? $validatedData['remarks'] : null
            ]);


            DB::commit();
            return response([
                'status' => true,
                'message' => 'Successfully marked penalty as paid',
                'method' => 'PATCH',
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response([
                'status' => false,
                'message' => 'An error occurred while updating transaction',
                'error' => $e->getMessage(),
                'method' => 'PATCH',
            ], 500);
        }
    }

    public function markAsUnpaid()
    {
        // Work in Progress
        // 
    }
}