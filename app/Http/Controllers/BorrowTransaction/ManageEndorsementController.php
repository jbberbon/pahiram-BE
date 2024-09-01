<?php

namespace App\Http\Controllers\BorrowTransaction;

use App\Http\Controllers\Controller;


use App\Http\Requests\ManageEndorsement\GetEndorsementRequest;
use App\Http\Requests\ManageEndorsement\EndorsementApprovalRequest;
use App\Http\Resources\BorrowTransactionResource;
use App\Http\Resources\EndorsementCollection;
use App\Models\BorrowedItem;
use App\Models\BorrowTransaction;
use App\Services\RetrieveStatusService\BorrowedItemStatusService;
use App\Services\RetrieveStatusService\BorrowTransactionStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ManageEndorsementController extends Controller
{
    private $disapprovedTransacStatus;
    private $pendingBorrowingApprovalTransacStatus;

    private $pendingBorrowedItemApprovalStatus;
    private $disapprovedBorrowedItemStatus;

    public function __construct()
    {
        $this->disapprovedTransacStatus = BorrowTransactionStatusService::getDisapprovedTransactionId();
        $this->pendingBorrowingApprovalTransacStatus = BorrowTransactionStatusService::getPendingBorrowingApprovalTransactionId();

        $this->pendingBorrowedItemApprovalStatus = BorrowedItemStatusService::getPendingStatusId();
        $this->disapprovedBorrowedItemStatus = BorrowedItemStatusService::getDisapprovedStatusId();
    }
    /**
     * Display all tagged endorsements
     */
    public function index()
    {
        try {
            $userId = Auth::id();
            $endorsementList = BorrowTransaction::where('endorsed_by', $userId)->get();

            $endorsementResource = new EndorsementCollection($endorsementList);
            return response()->json([
                'status' => true,
                'data' => $endorsementResource,
                'method' => "GET"
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching borrowing requests',
                'error' => $e->getMessage(),
                'method' => 'GET',
            ], 500);
        }
    }

    /**
     * Get specific Borrow request
     */
    public function show(GetEndorsementRequest $request)
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
                    // 'item_groups.id', Not Needed in front end
                    'item_groups.model_name',
                    \DB::raw('COUNT(borrowed_items.id) as quantity'),
                    'borrowed_items.start_date',
                    'borrowed_items.due_date',
                    'borrowed_item_statuses.borrowed_item_status'
                )
                ->get();


            // Restructured due to the table in react needing the item field haha
            // already fixed
            // $restructuredItems = $items
            //     ->map(function ($item) {
            //         return [
            //             'item' => [
            //                 'model_name' => $item->model_name,
            //                 // 'id' => $item->id,
            //             ],
            //             'quantity' => $item->quantity,
            //             'start_date' => $item->start_date,
            //             'due_date' => $item->due_date,
            //             'borrowed_item_status' => $item->borrowed_item_status,
            //         ];
            //     });

            return response()->json([
                'status' => true,
                'data' => [
                    'transac_data' => new BorrowTransactionResource($transacData),
                    'items' => $items,
                ],
                'method' => "GET"
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "An error occured, try again later",
                'error' => $e,
                'method' => "GET"
            ], 500);
        }
    }

    /**
     * Approve / Decline Endorsement
     */
    public function endorsementApproval(EndorsementApprovalRequest $request)
    {
        $requestData = $request->validated();
        $transacId = $requestData['transactionId'];
        $approval = $requestData['approval']; // Boolean

        try {
            DB::beginTransaction();

            $transaction = BorrowTransaction::find($transacId);
            if ($approval) {
                // Pending Endorser Approval ---> Pending Borrowing Approval
                $transaction->update(['transac_status_id' => $this->pendingBorrowingApprovalTransacStatus]);

                DB::commit();
                // Item status remains the same --> Pending Approval
                return response()->json([
                    'status' => true,
                    'message' => "Successfully approved transaction",
                    'method' => "PATCH"
                ], 200);
            }
            // Pending Endorser Approval ---> Disapproved
            $transaction->update(['transac_status_id' => $this->disapprovedTransacStatus]);

            // Pending Approval ---> Disapproved
            BorrowedItem::where('borrowing_transac_id', $transacId)
                ->where('borrowed_item_status_id', $this->pendingBorrowedItemApprovalStatus)
                ->update(['borrowed_item_status_id' => $this->disapprovedBorrowedItemStatus]);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Successfully declined transaction",
                'method' => "PATCH"
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'error' => $e,
                'method' => "PATCH"
            ], 200);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BorrowTransaction $borrowTransaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BorrowTransaction $borrowTransaction)
    {
        //
    }
}
