<?php

namespace App\Http\Controllers\BorrowTransaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\ManageBorrowTransaction\GetSpecificItemsOfBorrowTransactionRequest;
use App\Models\BorrowedItem;
use App\Services\RetrieveStatusService\BorrowedItemStatusService;
use Carbon\Carbon;
use DB;

class GeneralBorrowTransactionController extends Controller
{
    private $cancelledBorrowItemStatus;
    public function __construct()
    {
        $this->cancelledBorrowItemStatus = BorrowedItemStatusService::getCancelledStatusId();
    }
    public function getSpecificItemsOfBorrowTransaction(GetSpecificItemsOfBorrowTransactionRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $transacId = $validatedData['transactionId'];
            $showPenaltyData = $validatedData['include-penalty-data'] ?? 'false' === 'true';

            $itemsQuery = BorrowedItem::where('borrowing_transac_id', $transacId)
                ->join('items', 'borrowed_items.item_id', '=', 'items.id')
                ->join('item_groups', 'items.item_group_id', '=', 'item_groups.id')
                ->leftJoin('borrowed_item_statuses', 'borrowed_items.borrowed_item_status_id', '=', 'borrowed_item_statuses.id')
                ->leftJoin('users as receiver', 'borrowed_items.receiver_id', '=', 'receiver.id')
                ->leftJoin('users as penalty_finalizer', 'borrowed_items.penalty_finalized_by', '=', 'penalty_finalizer.id')
                ->select(
                    'item_groups.model_name',
                    'item_groups.is_required_supervisor_approval',
                    'items.apc_item_id as apc_id',
                    'borrowed_items.id as borrowed_item_id',
                    'borrowed_items.start_date',
                    'borrowed_items.due_date',
                    'borrowed_items.date_returned',
                    'borrowed_items.penalty',
                    'borrowed_item_statuses.borrowed_item_status',
                    DB::raw("CONCAT(receiver.first_name, ' ', receiver.last_name) as receiver_name"),
                    'borrowed_items.remarks_by_receiver',
                    DB::raw("CONCAT(penalty_finalizer.first_name, ' ', penalty_finalizer.last_name) as penalty_finalizer_name"),
                    'borrowed_items.remarks_by_penalty_finalizer'
                );

            $items = $itemsQuery->get()->groupBy('model_name');

            $restructuredItems = $items->map(function ($groupedItem, $modelName) use ($showPenaltyData) {
                $item = $groupedItem->first(); // Assuming all items in the group share the same dates

                $result = [
                    'model_name' => $modelName,
                    'is_required_supervisor_approval' => (bool) $item->is_required_supervisor_approval,
                    'quantity' => $groupedItem->count(),
                    'start_date' => Carbon::parse($item->start_date)->format('Y-m-d H:i:s'),
                    'due_date' => Carbon::parse($item->due_date)->format('Y-m-d H:i:s'),
                    'items' => $groupedItem->map(function ($item) use ($showPenaltyData) {
                        $data = [
                            'borrowed_item_id' => $item->borrowed_item_id,
                            'borrowed_item_status' => $item->borrowed_item_status ?? 'Unknown',
                            'item_apc_id' => $item->apc_id,
                        ];
                        if ($showPenaltyData) {
                            $data['penalty'] = $item->penalty;
                            $data['receiver_name'] = $item->receiver_name;
                            $data['remarks_by_receiver'] = $item->remarks_by_receiver;
                            $data['penalty_finalizer'] = $item->penalty_finalizer_name;
                            $data['remarks_by_penalty_finalizer'] = $item->remarks_by_penalty_finalizer;
                        }
                        return $data;
                    }),
                ];

                return $result;
            });

            return response()->json([
                'status' => true,
                'data' => $restructuredItems,
                'method' => 'GET'
            ], 200);
        } catch (\Exception) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'method' => 'GET'
            ], 500);
        }
    }
}