<?php

namespace App\Http\Controllers\Penalty;

use App\Http\Controllers\Controller;
use App\Http\Requests\ManagePenalty\FinalizeLendingOfficePenaltyRequest;
use App\Http\Resources\ManagePenalty\LendingOffice\PenalizedTransactionCollection;
use App\Models\BorrowedItem;
use App\Models\BorrowTransaction;
use App\Models\PenalizedTransaction;
use App\Models\UserDepartment;
use App\Services\RetrieveStatusService\BorrowedItemStatusService;
use App\Services\RetrieveStatusService\PenalizedTransactionStatusService;
use Auth;
use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ManagePenalizedLendingTransactionController extends Controller
{
    private $pendingLendingSupervisorApprovalStatusId;
    private $settledAtLendingOfficeStatusId;
    private $penalizedTransacStatusHashmap;
    private $penalizedBorrowedItemStatusIds;
    private $pendingPaymentStatusId;
    public function __construct()
    {
        $this->pendingLendingSupervisorApprovalStatusId = PenalizedTransactionStatusService::getPendingLendingSupervisorFinalization();
        $this->settledAtLendingOfficeStatusId = PenalizedTransactionStatusService::getSettledAtLendingOfficeStatusId();
        $this->penalizedTransacStatusHashmap = PenalizedTransactionStatusService::hashmap();

        $this->penalizedBorrowedItemStatusIds = BorrowedItemStatusService::getPenalizedStatusIds();
        $this->pendingPaymentStatusId = PenalizedTransactionStatusService::getPendingPaymentStatusId();
    }
    public function index(Request $request)
    {
        try {
            // Get authenticated user and their department
            $user = Auth::user();
            $userDepartment = UserDepartment::where('user_id', $user->id)->first();

            if (!$userDepartment) {
                return response()->json([
                    'status' => false,
                    'message' => 'User does not belong to any department',
                    'method' => "GET"
                ], 404);
            }

            $itemsPerPage = 10;
            if (
                $request->has('per-page') &&
                $request->filled('per-page') &&
                is_numeric($request['per-page'])
            ) {
                $itemsPerPage = max(1, min((int) $request['per-page'], 100)); // Limit to 1–100 items
            }

            $penalizedTransacs = new Collection();
            // Check if the status exists in the request and is not empty
            if ($request->has('status') && $request->filled('status')) {
                // Fetch the corresponding status ID directly from the hashmap
                $statusId = $this->penalizedTransacStatusHashmap[$request['status']] ?? null;

                if ($statusId) {
                    // Start the query for filtering transactions by department and penalized status
                    $penalizedTransacs = BorrowTransaction::where('department_id', $userDepartment->department_id)
                        ->join('penalized_transactions', 'borrow_transactions.id', '=', 'penalized_transactions.borrowing_transac_id')
                        ->where('penalized_transactions.status_id', $statusId)
                        ->select(
                            'borrow_transactions.id as id',
                            'borrow_transactions.borrower_id',
                            'borrow_transactions.transac_status_id',
                            'borrow_transactions.purpose_id',
                            'borrow_transactions.user_defined_purpose',
                            'borrow_transactions.remarks_by_return_facilitator as remarks_by_return_facilitator',
                            'borrow_transactions.created_at'
                        )
                        ->paginate($itemsPerPage);

                    return response()->json([
                        'status' => true,
                        'data' => $penalizedTransacs,
                        'method' => "GET"
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Invalid penalized transaction status',
                        'method' => "GET"
                    ], 404);
                }
            }

            // No transac status was provided in URL Params
            $penalizedTransacs = BorrowTransaction::where('department_id', $userDepartment->department_id)
                ->join('penalized_transactions', 'borrow_transactions.id', '=', 'penalized_transactions.borrowing_transac_id')
                ->join('penalized_transaction_statuses', 'penalized_transactions.status_id', '=', 'penalized_transaction_statuses.id')
                ->select(
                    'borrow_transactions.id as id',
                    'borrow_transactions.department_id',
                    'borrow_transactions.borrower_id',
                    'borrow_transactions.transac_status_id',
                    'borrow_transactions.purpose_id',
                    'borrow_transactions.endorsed_by',
                    'borrow_transactions.user_defined_purpose',
                    'penalized_transaction_statuses.status as penalized_transaction_status',
                    'borrow_transactions.penalty',
                    'borrow_transactions.created_at',
                )
                ->paginate($itemsPerPage);

            return response()->json([
                'status' => true,
                'data' => new PenalizedTransactionCollection($penalizedTransacs),
                'method' => "GET"
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e,
                'message' => "Something went wrong while fetching penalized transactions list.",
                'method' => "GET"
            ], 500);
        }
    }

    public function finalizeLendingOfficePenalty(FinalizeLendingOfficePenaltyRequest $request)
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            $transacId = $validated['transactionId'];
            $items = $validated['items'];

            foreach ($items as $item) {
                $itemId = $item['borrowed_item_id'];
                $hasPenaltyChange = isset($validated['no_penalty_amt_change']);

                $updatedValues = [
                    'remarks_by_penalty_finalizer' => $item['remarks_by_penalty_finalizer'],
                    'penalty_finalized_by' => Auth::id()
                ];

                // \Log::info('CONTROLLER', [$validated]);
                // Only add 'penalty' if there is no penalty change
                if (!$hasPenaltyChange && isset($item['penalty'])) {
                    \Log::info('CONTROLLER', [$item['penalty']]);
                    $updatedValues['penalty'] = $item['penalty'];
                }

                BorrowedItem::where('id', $itemId)
                    ->update($updatedValues);
            }

            // Check every penalized item of borrowed items in the transaction.
            // if all penalized items' penalty_finalized_by field is NOT NULL, 
            // Change Penalized Transac Status to Pending Payment
            $adjustedPenaltyAmt = BorrowedItem::where('borrowing_transac_id', $transacId)
                ->whereIn('borrowed_item_status_id', $this->penalizedBorrowedItemStatusIds)
                ->whereNull('penalty_finalized_by')
                ->count();

            if ($adjustedPenaltyAmt === 0) {
                PenalizedTransaction::where('borrowing_transac_id', $transacId)
                    ->update([
                        'status_id' => $this->pendingPaymentStatusId
                    ]);
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Successfully finalized items' penalty amount.",
                'method' => "GET"
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
                'message' => "Something went wrong while finalizing penalty amount.",
                'method' => "GET"
            ], 500);
        }


    }
}
