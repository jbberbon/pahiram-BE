<?php

namespace App\Http\Controllers\BorrowTransaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\BorrowTransaction\BookedDatesRequest;
use App\Http\Requests\ManageBorrowTransaction\GetSpecificItemsOfBorrowTransactionRequest;
use App\Http\Resources\BorrowedItemResource;
use App\Models\BorrowedItem;
use App\Models\Item;
use App\Models\User;
use App\Services\RetrieveStatusService\BorrowedItemStatusService;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
            $request = $request->validated();
            $transacId = $request['transactionId'];

            // Fetch and restructure the borrowed items
            $items = BorrowedItem::where('borrowing_transac_id', $transacId)
                ->join('items', 'borrowed_items.item_id', '=', 'items.id')
                ->join('item_groups', 'items.item_group_id', '=', 'item_groups.id')
                ->join( // Use leftJoin to allow for null statuses
                    'borrowed_item_statuses',
                    'borrowed_items.borrowed_item_status_id',
                    '=',
                    'borrowed_item_statuses.id'
                )
                ->select(
                    'item_groups.model_name',
                    'items.apc_item_id as apc_id',
                    'borrowed_items.id as borrowed_item_id',
                    'borrowed_items.start_date',
                    'borrowed_items.due_date',
                    'borrowed_item_statuses.borrowed_item_status',
                    'borrowed_item_statuses.id as borrowed_item_status_id'
                )
                ->get();

            // Group the items by model_name
            $groupedItems = $items->groupBy('model_name');
            $restructuredItems = collect();

            // Restructure the grouped items
            foreach ($groupedItems as $modelName => $groupedItem) {
                $item = $groupedItem->first(); // Assuming all items in the group share the same dates
                $restructuredItems->push([
                    'model_name' => $modelName,
                    'quantity' => $groupedItem->count(),
                    'start_date' => Carbon::parse($item->start_date)->format('Y-m-d H:i:s'),
                    'due_date' => Carbon::parse($item->due_date)->format('Y-m-d H:i:s'),
                    'items' => $groupedItem->map(function ($item) {
                        return [
                            'borrowed_item_id' => $item->borrowed_item_id,
                            'borrowed_item_status' => $item->borrowed_item_status ?? 'Unknown',
                            'item_apc_id' => $item->apc_id
                        ];
                    })
                ]);
            }

            return response()->json([
                'status' => true,
                'data' => $restructuredItems,
                'method' => 'GET'
            ], 200);
        } catch (\Exception $exception) {
            // Handle other exceptions
            return response()->json([
                'status' => false,
                'error' => 'Something went wrong',
                'method' => 'GET'
            ], 500);
        }
    }
}