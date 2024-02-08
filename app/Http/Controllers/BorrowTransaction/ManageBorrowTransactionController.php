<?php

namespace App\Http\Controllers\BorrowTransaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\BorrowTransaction\GetBorrowTransactionRequest;
use App\Http\Requests\ManageBorrowTransaction\ApproveTransactionRequest;
use App\Http\Requests\ManageBorrowTransaction\GetSpecificPendingTransactionRequest;
use App\Http\Resources\BorrowTransactionResource;
use App\Models\BorrowedItem;
use App\Models\BorrowedItemStatus;
use App\Models\BorrowTransaction;
use App\Models\BorrowTransactionStatus;
use App\Models\Department;
use App\Models\UserDepartment;
use App\Utils\Constants\OfficeCodes;
use App\Utils\Constants\Statuses\BORROWED_ITEM_STATUS;
use App\Utils\Constants\Statuses\TRANSAC_STATUS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ManageBorrowTransactionController extends Controller
{
    private $pendingItemApprovalId;
    private $approvedItemStatusId;
    private $disapprovedItemStatusId;

    private $disapprovedTransacStatusId;
    private $approvedTransacStatusId;
    public function __construct()
    {
        $this->pendingItemApprovalId = BorrowedItemStatus::getIdByStatus(BORROWED_ITEM_STATUS::PENDING_APPROVAL);
        $this->approvedItemStatusId = BorrowedItemStatus::getIdByStatus(BORROWED_ITEM_STATUS::APPROVED);
        $this->disapprovedItemStatusId = BorrowedItemStatus::getIdByStatus(BORROWED_ITEM_STATUS::DISAPPROVED);

        $this->approvedTransacStatusId = BorrowTransactionStatus::getIdByStatus(TRANSAC_STATUS::APPROVED);
        $this->disapprovedTransacStatusId = BorrowTransactionStatus::getIdByStatus(TRANSAC_STATUS::DISAPPROVED);

    }
    /**
     * Display transaction list of LENDING OFFICE
     */
    public function index()
    {
        // Check from which office the user belongs
        $user = Auth::user();
        $userDepartment = UserDepartment::where('user_id', $user->id)->first();

        $transactions = BorrowTransaction::where('department_id', $userDepartment->department_id)
            ->get()
            ->toArray();

        return response([
            'status' => true,
            'data' => $transactions,
            'method' => "GET"
        ]);
    }

    /**
     * Get specific Borrow request
     */
    public function getSpecificPendingTransaction(GetSpecificPendingTransactionRequest $request)
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
                    'item_groups.id',
                    'item_groups.model_name',
                    \DB::raw('COUNT(borrowed_items.id) as quantity'),
                    'borrowed_items.start_date',
                    'borrowed_items.due_date',
                    'borrowed_item_statuses.borrowed_item_status'
                )
                ->get();


            // Restructured due to the table in react needing the item field haha
            // Already fixed
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

            return response([
                'status' => true,
                'data' => [
                    'transac_data' => new BorrowTransactionResource($transacData),
                    'items' => $items,
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
    public function approveTransaction(ApproveTransactionRequest $request)
    {
        $validatedData = $request->validated();
        $transacId = $validatedData['transactionId'];

        if (isset($validatedData['approve_all_items']) && !isset($validatedData['items'])) {
            $isTransacApprovedStatus = ($validatedData['approve_all_items'] === true) ? $this->approvedTransacStatusId : $this->disapprovedTransacStatusId;
            $isItemApprovedStatus = ($validatedData['approve_all_items'] === true) ? $this->approvedItemStatusId : $this->disapprovedItemStatusId;
            try {
                DB::beginTransaction();
                // Update items status
                BorrowedItem::where('borrowing_transac_id', $transacId)
                    ->where('borrowed_item_status_id', $this->pendingItemApprovalId)
                    ->join('items', 'items.id', '=', 'borrowed_items.item_id')
                    ->update(['borrowed_items.borrowed_item_status_id' => $isItemApprovedStatus]);

                // Update transaction status
                BorrowTransaction::find($transacId)->update(['transac_status_id' => $isTransacApprovedStatus]);

                DB::commit();
                return response([
                    'status' => true,
                    'message' => 'Successfully updated transaction status',
                    'method' => "GET"
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
        }


        if (!isset($validatedData['approve_all_items']) && isset($validatedData['items'])) {
            try {
                DB::beginTransaction();
                foreach ($validatedData['items'] as $item) {
                    $isItemApprovedStatus = ($item['is_approved'] === true) ? $this->approvedItemStatusId : $this->disapprovedItemStatusId;
                    // Update items status
                    BorrowedItem::where('borrowing_transac_id', $transacId)
                        ->where('borrowed_item_status_id', $this->pendingItemApprovalId)
                        ->join('items', 'items.id', '=', 'borrowed_items.item_id')
                        ->where('items.item_group_id', $item['item_group_id'])
                        ->update(['borrowed_items.borrowed_item_status_id' => $isItemApprovedStatus]);
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
                    BorrowTransaction::find($transacId)->update(['transac_status_id' => $this->disapprovedTransacStatusId]);
                }
                // if No more pending items && there are SOME approved items
                // then, the transaction is APPROVED
                if ($pendingApprovalItemCount == 0 && $approvedItemCount > 0) {
                    BorrowTransaction::find($transacId)->update(['transac_status_id' => $this->approvedTransacStatusId]);
                }

                DB::commit();
                return response([
                    'status' => true,
                    'message' => 'Successfully updated transaction status',
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
        }

        // Error CATCH
        return response([
            'status' => false,
            'message' => 'Failed to approve transaction',
            'method' => "PATCH"
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
