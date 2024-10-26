<?php

namespace App\Http\Controllers\BorrowTransaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\ManageBorrowTransaction\ApproveTransactionRequest;
use App\Http\Requests\ManageBorrowTransaction\ApproveTransactionRequestV2;
use App\Http\Requests\ManageBorrowTransaction\FacilitateReturnRequest;
use App\Http\Requests\ManageBorrowTransaction\GetSpecificPendingTransactionRequest;
use App\Http\Requests\ManageBorrowTransaction\ReleaseApprovedItemRequest;
use App\Http\Resources\BorrowedItemCollection;
use App\Http\Resources\BorrowTransaction\BorrowTransactionResource;
use App\Http\Resources\BorrowTransaction\BorrowTransactionCollection;
use App\Models\BorrowedItem;
use App\Models\BorrowedItemStatus;
use App\Models\BorrowTransaction;
use App\Models\BorrowTransactionStatus;
use App\Models\Item;
use App\Models\ItemStatus;
use App\Models\PenalizedTransaction;
use App\Models\PenalizedTransactionStatuses;
use App\Models\UserDepartment;
use App\Services\CalculatePenalty;
use App\Utils\Constants\Statuses\BORROWED_ITEM_STATUS;
use App\Utils\Constants\Statuses\ITEM_STATUS;
use App\Utils\Constants\Statuses\PENALIZED_TRANSAC_STATUS;
use App\Utils\Constants\Statuses\TRANSAC_STATUS;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ManageBorrowTransactionController extends Controller
{
    private $pendingItemApprovalId;
    private $approvedItemStatusId;
    private $disapprovedItemStatusId;
    private $inpossessionItemStatusId;
    private $unreleasedItemStatusId;
    private $returnedItemStatusId;
    private $unreturnedItemStatusId;
    private $damageButRepairableItemStatusId;
    private $unrepairableItemStatusId;
    private $lostItemStatusId;
    // ----
    private $pendingBorrowingApprovalStatusId;
    private $pendingEndorserApprovalStatusId;
    private $disapprovedTransacStatusId;
    private $approvedTransacStatusId;
    private $ongoingTransacStatusId;
    private $unreleasedTransacStatusId;
    private $completeTransacStatusId;
    private $unreturnedTransacStatusId;

    protected $calculatePenalty;
    private $pendingPenaltyPaymentStatusId;

    // Item Statuses
    private $lostInventoryStatusId;
    private $unreturnedInventoryStatusId;
    private $forRepairInventoryStatusId;
    private $beyondRepairInventoryStatusId;


    public function __construct(CalculatePenalty $calculatePenalty)
    {
        $this->calculatePenalty = $calculatePenalty;

        $this->pendingItemApprovalId = BorrowedItemStatus::getIdByStatus(BORROWED_ITEM_STATUS::PENDING_APPROVAL);
        $this->approvedItemStatusId = BorrowedItemStatus::getIdByStatus(BORROWED_ITEM_STATUS::APPROVED);
        $this->disapprovedItemStatusId = BorrowedItemStatus::getIdByStatus(BORROWED_ITEM_STATUS::DISAPPROVED);
        $this->inpossessionItemStatusId = BorrowedItemStatus::getIdByStatus(BORROWED_ITEM_STATUS::IN_POSSESSION);
        $this->unreleasedItemStatusId = BorrowedItemStatus::getIdByStatus(BORROWED_ITEM_STATUS::UNRELEASED);
        $this->returnedItemStatusId = BorrowedItemStatus::getIdByStatus(BORROWED_ITEM_STATUS::RETURNED);
        $this->unreturnedItemStatusId = BorrowedItemStatus::getIdByStatus(BORROWED_ITEM_STATUS::UNRETURNED);
        $this->damageButRepairableItemStatusId = BorrowedItemStatus::getIdByStatus(BORROWED_ITEM_STATUS::DAMAGED_BUT_REPAIRABLE);
        $this->unrepairableItemStatusId = BorrowedItemStatus::getIdByStatus(BORROWED_ITEM_STATUS::UNREPAIRABLE);
        $this->lostItemStatusId = BorrowedItemStatus::getIdByStatus(BORROWED_ITEM_STATUS::LOST);

        $this->pendingEndorserApprovalStatusId = BorrowTransactionStatus::getIdByStatus(TRANSAC_STATUS::PENDING_ENDORSER_APPROVAL);
        $this->pendingBorrowingApprovalStatusId = BorrowTransactionStatus::getIdByStatus(TRANSAC_STATUS::PENDING_BORROWING_APPROVAL);
        $this->approvedTransacStatusId = BorrowTransactionStatus::getIdByStatus(TRANSAC_STATUS::APPROVED);
        $this->disapprovedTransacStatusId = BorrowTransactionStatus::getIdByStatus(TRANSAC_STATUS::DISAPPROVED);
        $this->ongoingTransacStatusId = BorrowTransactionStatus::getIdByStatus(TRANSAC_STATUS::ON_GOING);
        $this->unreleasedTransacStatusId = BorrowTransactionStatus::getIdByStatus(TRANSAC_STATUS::UNRELEASED);
        $this->completeTransacStatusId = BorrowTransactionStatus::getIdByStatus(TRANSAC_STATUS::TRANSACTION_COMPLETE);
        $this->unreturnedTransacStatusId = BorrowTransactionStatus::getIdByStatus(TRANSAC_STATUS::UNRETURNED);

        $this->pendingPenaltyPaymentStatusId = PenalizedTransactionStatuses::getIdByStatus(PENALIZED_TRANSAC_STATUS::PENDING_PAYMENT);

        // Item inventory status
        $this->lostInventoryStatusId = ItemStatus::getIdByStatus(ITEM_STATUS::LOST);
        $this->unreturnedInventoryStatusId = ItemStatus::getIdByStatus(ITEM_STATUS::UNRETURNED);
        $this->forRepairInventoryStatusId = ItemStatus::getIdByStatus(ITEM_STATUS::FOR_REPAIR);
        $this->beyondRepairInventoryStatusId = ItemStatus::getIdByStatus(ITEM_STATUS::BEYOND_REPAIR);


    }
    /**
     * Display transaction list of LENDING OFFICE
     */
    public function index(Request $request)
    {
        // Get authenticated user and their department
        $user = Auth::user();
        $userDepartment = UserDepartment::where('user_id', $user->id)->first();

        if (!$userDepartment) {
            return response()->json([
                'status' => false,
                'message' => 'User department not found',
                'method' => "GET"
            ], 404);
        }

        // Start the query for filtering transactions by department
        $transactions = BorrowTransaction::where('department_id', $userDepartment->department_id);

        // Filter by lapsed approval (past start date)
        if ($request->has('is_lapsed') && $request['is_lapsed']) {
            $transactions->where('transac_status_id', $this->pendingBorrowingApprovalStatusId)
                ->join('borrowed_items', 'borrow_transactions.id', '=', 'borrowed_items.borrowing_transac_id')
                ->where('borrowed_items.start_date', '<', Carbon::now()->toDateTimeString())
                ->groupBy('borrowed_items.borrowing_transac_id')
                ->select(
                    'borrow_transactions.id as id',
                    'borrow_transactions.borrower_id',
                    'borrow_transactions.transac_status_id',
                    'borrow_transactions.purpose_id',
                    'borrow_transactions.user_defined_purpose',
                    'borrow_transactions.created_at'
                );
        }

        // Filter by transaction status
        if ($request->has('status')) {
            $transacStatusId = BorrowTransactionStatus::getIdByStatus($request['status']);
            $transactions->where('transac_status_id', $transacStatusId);

            // Additional filtering for pending borrowing approval
            if ($request['status'] === 'PENDING_BORROWING_APPROVAL') {
                $transactions->join('borrowed_items', 'borrow_transactions.id', '=', 'borrowed_items.borrowing_transac_id')
                    ->where('borrowed_items.start_date', '>', Carbon::now()->toDateTimeString())
                    ->groupBy('borrowed_items.borrowing_transac_id')
                    ->select(
                        'borrow_transactions.id as id',
                        'borrow_transactions.borrower_id',
                        'borrow_transactions.transac_status_id',
                        'borrow_transactions.purpose_id',
                        'borrow_transactions.user_defined_purpose',
                        'borrow_transactions.created_at'
                    );
            }
        }

        // Define the number of results per page 
        $perPage = $request->get('per_page', 8);

        // Paginate the filtered transactions
        $paginatedTransactions = $transactions->paginate($perPage);

        // Return the paginated transactions using the EndorsementCollection
        return response()->json([
            'status' => true,
            'data' => new BorrowTransactionCollection($paginatedTransactions),
            'method' => "GET"
        ]);
    }

    /**
     * Get specific Borrow request
     */
    public function getSpecificPendingTransaction(GetSpecificPendingTransactionRequest $request)
    {
        $request = $request->validated();
        // return $request;
        $transacId = $request['transactionId'];
        $transacData = BorrowTransaction::find($transacId);

        if (!isset($request['view_individual_items'])) {
            try {
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
                        'borrowed_items.id' // Include borrowed_items.id in groupBy
                    )
                    ->select(
                        'borrowed_items.id', // Select the borrowed item ID
                        'item_groups.id as item_group_id', // Renaming to avoid ambiguity
                        'item_groups.model_name',
                        \DB::raw('COUNT(borrowed_items.id) as quantity'),
                        'borrowed_items.start_date',
                        'borrowed_items.due_date',
                        'borrowed_item_statuses.borrowed_item_status',
                        'items.apc_item_id as items_apc_id',
                    )
                    ->get();

                // $isSupervisorApprovalReqd = BorrowedItem::where('borrowing_transac_id', $transacId)
                //     ->where('borrowed_item_status_id', $this->pendingItemApprovalId)
                //     ->join('items', 'borrowed_items.item_id', '=', 'items.id')
                //     ->join('item_groups', 'items.item_group_id', '=', 'item_groups.id')
                //     ->where('item_groups.is_required_supervisor_approval', true)
                //     ->count();

                // // Checks if any pending approval item is overdue for approval
                // // Current Time and Date > Item start date 
                // $isApprovalOverdue = BorrowedItem::where('borrowing_transac_id', $transacId)
                //     ->where('borrowed_item_status_id', $this->pendingItemApprovalId)
                //     ->where('start_date', '<', Carbon::now()->toDateTimeString()) // Where start date is greater than the current time
                //     ->count();

                $formattedTransacData = new BorrowTransactionResource(
                    $transacData
                    // $isSupervisorApprovalReqd > 0,
                    // $isApprovalOverdue > 0
                );

                return response()->json([
                    'status' => true,
                    'data' => [
                        'transac_data' => $formattedTransacData,
                        'items' => $items,
                    ],
                    'method' => "GET"
                ], 200);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => "An error occurred, try again later",
                    'error' => $e,
                    'method' => "GET"
                ], 500);
            }
        }


        if (isset($request['view_individual_items'])) {
            try {
                $formattedTransacData = new BorrowTransactionResource($transacData);

                $items = BorrowedItem::where('borrowing_transac_id', $transacId)
                    ->get();

                return response([
                    'status' => true,
                    'data' => [
                        'transac_data' => $formattedTransacData,
                        'items' => new BorrowedItemCollection($items),
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
    }

    public function approveTransaction(ApproveTransactionRequest $request)
    {
        try {
            DB::beginTransaction();
            $validatedData = $request->validated();
            $transacId = $validatedData['transactionId'];
            $transaction = BorrowTransaction::find($transacId);

            $approverId = Auth::user()->id;

            if (
                isset($validatedData['approve_all_items']) &&
                !isset($validatedData['items'])
            ) {
                $transacStatusId = ($validatedData['approve_all_items'] === true) ?
                    $this->approvedTransacStatusId :
                    $this->disapprovedTransacStatusId;

                $borrowedItemStatus = ($validatedData['approve_all_items'] === true) ?
                    $this->approvedItemStatusId :
                    $this->disapprovedItemStatusId;


                // Update items status
                BorrowedItem::where('borrowing_transac_id', $transacId)
                    ->where('borrowed_item_status_id', $this->pendingItemApprovalId)
                    ->update([
                        'borrowed_items.borrowed_item_status_id' => $borrowedItemStatus,
                        'borrowed_items.approver_id' => $approverId,
                    ]);

                // Update transaction status
                $transaction->update([
                    'transac_status_id' => $transacStatusId
                ]);

                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'Successfully updated transaction status',
                    'method' => "PATCH"
                ], 200);
            }

            if (
                !isset($validatedData['approve_all_items']) &&
                isset($validatedData['items'])
            ) {
                foreach ($validatedData['items'] as $item) {
                    $isItemApprovedStatus = ($item['is_approved'] === true) ?
                        $this->approvedItemStatusId :
                        $this->disapprovedItemStatusId;

                    // Update items status to approved or disapproved
                    BorrowedItem::where('id', $item['borrowed_item_id'])
                        ->update([
                            'borrowed_item_status_id' => $isItemApprovedStatus,
                            'approver_id' => $approverId,
                        ]);
                }

                // Update Transaction Status
                $pendingApprovalItemCount = BorrowedItem::where('borrowing_transac_id', $transacId)
                    ->where('borrowed_item_status_id', $this->pendingItemApprovalId)
                    ->count();
                $approvedItemCount = BorrowedItem::where('borrowing_transac_id', $transacId)
                    ->where('borrowed_item_status_id', $this->approvedItemStatusId)
                    ->count();

                // If no more pending items && no approved items 
                // then, the whole transaction is DISAPPROVED
                if ($pendingApprovalItemCount == 0 && $approvedItemCount == 0) {
                    $transaction->update([
                        'transac_status_id' => $this->disapprovedTransacStatusId
                    ]);
                }

                // if No more pending items && there are SOME approved items
                // then, the transaction is APPROVED
                if ($pendingApprovalItemCount == 0 && $approvedItemCount > 0) {
                    $transaction->update([
                        'transac_status_id' => $this->approvedTransacStatusId
                    ]);
                }

                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'Successfully updated transaction status',
                    'method' => "PATCH"
                ], 200);
            }

            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to approve transaction',
                'method' => "PATCH"
            ], 500);
        } catch (\Exception) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to approve transaction',
                'method' => "PATCH"
            ], 500);
        }
    }


    /**
     * Release Approved Items
     */
    public function releaseApprovedItems(ReleaseApprovedItemRequest $request)
    {
        try {
            DB::beginTransaction();
            $validatedData = $request->validated();
            $transacId = $validatedData['transactionId'];
            $transaction = BorrowTransaction::find($transacId);

            $releaserId = Auth::user()->id;

            if (
                isset($validatedData['release_all_items']) &&
                !isset($validatedData['items'])
            ) {
                $transacStatusId = ($validatedData['release_all_items'] === true) ?
                    $this->ongoingTransacStatusId :
                    $this->unreleasedTransacStatusId;

                $borrowedItemStatusId = ($validatedData['release_all_items'] === true) ?
                    $this->inpossessionItemStatusId :
                    $this->unreleasedItemStatusId;

                // Update items status
                BorrowedItem::where('borrowing_transac_id', $transacId)
                    ->where('borrowed_item_status_id', $this->approvedItemStatusId)
                    ->update([
                        'borrowed_items.borrowed_item_status_id' => $borrowedItemStatusId
                    ]);

                // Update transaction status
                $transaction->update([
                    'transac_status_id' => $transacStatusId
                ]);

                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'Successfully released all items',
                    'method' => "PATCH"
                ], 200);
            }

            if (
                !isset($validatedData['approve_all_items']) &&
                isset($validatedData['items'])
            ) {
                foreach ($validatedData['items'] as $item) {
                    $borrowedItemStatusId = ($item['is_released'] === true) ?
                        $this->inpossessionItemStatusId :
                        $this->unreleasedItemStatusId;

                    // Update item status
                    $borrowedItem = BorrowedItem::findOrfail($item['borrowed_item_id']);
                    $borrowedItem->update([
                        'borrowed_item_status_id' => $borrowedItemStatusId,
                        'releaser_id' => $releaserId,
                    ]);
                }

                // Update Transaction Status
                $approvedItemCount = BorrowedItem::where('borrowing_transac_id', $transacId)
                    ->where('borrowed_item_status_id', $this->approvedItemStatusId)
                    ->count();
                $inpossessionItemCount = BorrowedItem::where('borrowing_transac_id', $transacId)
                    ->where('borrowed_item_status_id', $this->inpossessionItemStatusId)
                    ->count();

                // If no more approved items && no in possession item
                // then, the whole transaction is UNRELEASED
                if ($approvedItemCount == 0 && $inpossessionItemCount == 0) {
                    $transaction->update([
                        'transac_status_id' => $this->unreleasedTransacStatusId
                    ]);
                }

                // If HAS approved items && HAS possession items 
                // then, the transaction is ONGOING
                if ($approvedItemCount > 0 && $inpossessionItemCount > 0) {
                    $transaction->update([
                        'transac_status_id' => $this->ongoingTransacStatusId
                    ]);
                }

                // If no more approved items && HAS possession items 
                // then, the transaction is ONGOING
                if ($approvedItemCount == 0 && $inpossessionItemCount > 0) {
                    $transaction->update([
                        'transac_status_id' => $this->ongoingTransacStatusId
                    ]);
                }

                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'Successfully released items',
                    'method' => "PATCH"
                ], 200);
            }

            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to approve transaction',
                'method' => "PATCH"
            ], 500);
        } catch (\Exception) {
            // Error CATCH
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong while releasing items',
                'method' => "PATCH"
            ], 500);
        }
    }

    /**
     * Facilitate Return
     */
    public function facilitateReturn(FacilitateReturnRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $transacId = $validatedData['transactionId'];
            $transaction = BorrowTransaction::find($transacId);
            $items = isset($validatedData['items']) ? $validatedData['items'] : null;
            $returnedStatuses = BORROWED_ITEM_STATUS::RETURNED_STATUSES;

            if ($items === null) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to update return status',
                    'method' => "PATCH"
                ], 500);
            }

            DB::beginTransaction();
            foreach ($items as $item) {
                $borrowedItemStatusId = BorrowedItemStatus::getIdByStatus($item['item_status']);
                // Update borrowed item status
                $borrowedItem = BorrowedItem::findOrfail($item['borrowed_item_id']);
                $dateReturned = in_array(
                    $item['item_status'],
                    array_values($returnedStatuses)
                ) ?
                    now()->format('Y-m-d H:i:s') :
                    null;

                if (isset($item['item_remarks']) && isset($item['item_penalty'])) {
                    $borrowedItem->update([
                        'borrowed_item_status_id' => $borrowedItemStatusId,
                        'remarks' => $item['item_remarks'],
                        'penalty' => $item['item_penalty'] + $this->calculatePenalty->penaltyAmount($item['borrowed_item_id']),
                        'date_returned' => $dateReturned
                    ]);
                }

                if (isset($item['item_remarks']) && !isset($item['item_penalty'])) {
                    $borrowedItem->update([
                        'borrowed_item_status_id' => $borrowedItemStatusId,
                        'remarks' => $item['item_remarks'],
                        'date_returned' => $dateReturned
                    ]);
                }

                if (!isset($item['item_remarks']) && isset($item['item_penalty'])) {
                    $borrowedItem->update([
                        'borrowed_item_status_id' => $borrowedItemStatusId,
                        'penalty' => $item['item_penalty'] + $this->calculatePenalty->penaltyAmount($item['borrowed_item_id']),
                        'date_returned' => $dateReturned
                    ]);
                }

                if (!isset($item['item_remarks']) && !isset($item['item_penalty'])) {
                    $latePenaltyAmount = $this->calculatePenalty->penaltyAmount($item['borrowed_item_id']);
                    if ($latePenaltyAmount < 0) {
                        $borrowedItem->update([
                            'borrowed_item_status_id' => $borrowedItemStatusId,
                            'date_returned' => $dateReturned
                        ]);
                    } else {
                        $borrowedItem->update([
                            'borrowed_item_status_id' => $borrowedItemStatusId,
                            'penalty' => $latePenaltyAmount,
                            'date_returned' => $dateReturned
                        ]);
                    }
                }


                // Update status of the item in inventory
                // Lost
                if ($borrowedItem->borrowed_item_status_id === $this->lostItemStatusId) {
                    Item::find($borrowedItem->item_id)
                        ->update(['item_status_id' => $this->lostInventoryStatusId]);
                }
                // Unreturned
                if ($borrowedItem->borrowed_item_status_id === $this->unreturnedItemStatusId) {
                    Item::find($borrowedItem->item_id)
                        ->update(['item_status_id' => $this->unreturnedInventoryStatusId]);
                }
                // Damaged
                if ($borrowedItem->borrowed_item_status_id === $this->damageButRepairableItemStatusId) {
                    Item::find($borrowedItem->item_id)
                        ->update([
                            'item_status_id' => $this->forRepairInventoryStatusId,
                        ]);
                }
                // Unrepairable
                if ($borrowedItem->borrowed_item_status_id === $this->unrepairableItemStatusId) {
                    Item::find($borrowedItem->item_id)
                        ->update([
                            'item_status_id' => $this->beyondRepairInventoryStatusId,
                        ]);
                }

            }

            // Update Transaction Status
            $inpossessionItemCount = BorrowedItem::where('borrowing_transac_id', $transacId)
                ->where('borrowed_item_status_id', $this->inpossessionItemStatusId)
                ->count();
            $returnedItemCount = BorrowedItem::where('borrowing_transac_id', $transacId)
                ->where('borrowed_item_status_id', $this->returnedItemStatusId)
                ->count();
            $damageButRepairableItemCount = BorrowedItem::where('borrowing_transac_id', $transacId)
                ->where('borrowed_item_status_id', $this->damageButRepairableItemStatusId)
                ->count();
            $unrepairableItemCount = BorrowedItem::where('borrowing_transac_id', $transacId)
                ->where('borrowed_item_status_id', $this->unrepairableItemStatusId)
                ->count();
            $unreturnedItemCount = BorrowedItem::where('borrowing_transac_id', $transacId)
                ->where('borrowed_item_status_id', $this->unreturnedItemStatusId)
                ->count();
            $lostItemCount = BorrowedItem::where('borrowing_transac_id', $transacId)
                ->where('borrowed_item_status_id', $this->lostItemStatusId)
                ->count();

            // $totalCount = $inpossessionItemCount + $damageButRepairableItemCount +
            //     $unrepairableItemCount + $lostItemCount + $returnedItemCount;

            $totalPenalty = BorrowedItem::where('borrowing_transac_id', $transacId)
                ->where('penalty', '>', 0)
                ->sum('penalty');

            $consolidatedReturnedItemCount = $returnedItemCount + $damageButRepairableItemCount + $unrepairableItemCount;
            $consolidatedUnreturnedItemCount = $unreturnedItemCount + $lostItemCount;

            // Transaction is UNRETURNED
            $isTransactionUnreturned =
                $inpossessionItemCount === 0 &&
                $consolidatedReturnedItemCount === 0 &&
                $consolidatedUnreturnedItemCount > 0;

            if ($isTransactionUnreturned) {
                if ($totalPenalty > 0) {
                    $transaction->update([
                        'transac_status_id' => $this->unreturnedTransacStatusId,
                        'penalty' => $totalPenalty
                    ]);

                    // Add transaction to PenalizedTransaction
                    $penalizedTransacExists = PenalizedTransaction::where('borrowing_transac_id', $transacId)->exists();
                    if (!$penalizedTransacExists) {
                        PenalizedTransaction::create([
                            'borrowing_transac_id' => $transacId,
                            'status_id' => $this->pendingPenaltyPaymentStatusId,
                        ]);
                    }
                } else {
                    $transaction->update([
                        'transac_status_id' => $this->unreturnedTransacStatusId,
                    ]);
                }
            }

            // Transaction is COMPLETE
            //  ->  inPossession > 0
            //  ->  returned > 0
            //  ->  damageButRepairable > 0
            //  ->  unrepairable > 0
            //  ->  lost > 0
            $isTransactionComplete = $consolidatedReturnedItemCount > 0 && $inpossessionItemCount == 0;

            if ($isTransactionComplete) {
                if ($totalPenalty > 0) {
                    $transaction->update([
                        'transac_status_id' => $this->completeTransacStatusId,
                        'penalty' => $totalPenalty
                    ]);

                    // Add transaction to PenalizedTransaction
                    $penalizedTransacExists = PenalizedTransaction::where('borrowing_transac_id', $transacId)->exists();
                    if (!$penalizedTransacExists) {
                        PenalizedTransaction::create([
                            'borrowing_transac_id' => $transacId,
                            'status_id' => $this->pendingPenaltyPaymentStatusId,
                        ]);
                    }
                } else {
                    $transaction->update([
                        'transac_status_id' => $this->completeTransacStatusId,
                    ]);
                }
            }

            DB::commit();
            return response([
                'status' => true,
                'message' => 'Successfully returned items',
                'method' => "PATCH"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'status' => false,
                'message' => "An error occured, try again later",
                'error' => $e,
                'method' => "PATCH"
            ], 500);
        }


        // return response([
        //     'status' => false,
        //     'message' => 'Failed to update return status',
        //     'method' => "PATCH"
        // ]);

    }
}
