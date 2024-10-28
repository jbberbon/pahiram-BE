<?php

namespace App\Http\Controllers\Penalty;

use App\Http\Controllers\Controller;
use App\Http\Resources\ManagePenalty\LendingOffice\PenalizedTransactionCollection;
use App\Models\BorrowTransaction;
use App\Models\UserDepartment;
use App\Services\RetrieveStatusService\PenalizedTransactionStatusService;
use Auth;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ManagePenalizedLendingTransactionController extends Controller
{
    private $pendingLendingSupervisorApprovalStatusId;
    private $settledAtLendingOfficeStatusId;

    private $penalizedTransacStatusHashmap;

    public function __construct()
    {
        $this->pendingLendingSupervisorApprovalStatusId = PenalizedTransactionStatusService::getPendingLendingSupervisorFinalization();
        $this->settledAtLendingOfficeStatusId = PenalizedTransactionStatusService::getSettledAtLendingOfficeStatusId();
        $this->penalizedTransacStatusHashmap = PenalizedTransactionStatusService::hashmap();

    }
    public function index(Request $request)
    {
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
                'borrow_transactions.borrower_id',
                'borrow_transactions.transac_status_id',
                'borrow_transactions.purpose_id',
                'borrow_transactions.user_defined_purpose',
                'borrow_transactions.remarks_by_return_facilitator',
                'penalized_transaction_statuses.status as penalized_transaction_status',
                'borrow_transactions.penalty',
                'borrow_transactions.created_at',
            )
            ->paginate($itemsPerPage);

        return response()->json([
            'status' => true,
            'data' => new PenalizedTransactionCollection($penalizedTransacs),
            'method' => "GET"
        ]);
    }

}
