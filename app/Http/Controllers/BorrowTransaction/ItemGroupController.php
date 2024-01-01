<?php

namespace App\Http\Controllers\BorrowTransaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\BorrowTransaction\BookedDatesRequest;
use App\Models\BorrowedItem;
use App\Models\BorrowedItemStatus;
use App\Models\ItemGroup;
use App\Models\Item;
use Illuminate\Http\Request;

class ItemGroupController extends Controller
{
    private $pendingStatusId;
    private $borrowedStatusId;
    public function __construct()
    {
        $this->pendingStatusId = BorrowedItemStatus::where('borrowed_item_status_code', 1010)->first()->id;
        $this->borrowedStatusId = BorrowedItemStatus::where('borrowed_item_status_code', 2020)->first()->id;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }
    /**
     *  Retrieve UNAVAILABLE dates
     */
    public function retrieveBookedDates(BookedDatesRequest $bookedDatesRequest)
    {
        $validatedData = $bookedDatesRequest->validated();
        $itemGroupId = $validatedData['itemGroupId'];


        // RAW Join method
        // User
        // SELECT item_groups.id AS item_group_id, 
        // items.id AS item_id, borrowed_items.borrowed_item_status_id, 
        // borrowed_items.start_date, borrowed_items.return_date
        // FROM item_groups                                             (table 1)
        // JOIN items ON item_groups.id = items.item_group_id           (table 2)
        // JOIN borrowed_items ON items.id = borrowed_items.items_id    (table 3)
        // WHERE (
        //     item_groups.id = $certain_item_group_ids
        // );
        try {
            $borrowedItems = BorrowedItem::join('items', 'borrowed_items.item_id', '=', 'items.id')
                ->join('item_groups', 'items.item_group_id', '=', 'item_groups.id')
                ->where('item_groups.id', $itemGroupId)
                ->where(function ($query) {
                    $query->where('borrowed_items.borrowed_item_status_id', $this->pendingStatusId)
                        ->orWhere('borrowed_items.borrowed_item_status_id', $this->borrowedStatusId);
                })
                ->select(
                    // 'item_groups.id AS item_group_id',
                    'items.id AS item_id',
                    // 'borrowed_items.borrowed_item_status_id',
                    'borrowed_items.start_date',
                    'borrowed_items.due_date'
                )
                ->get();

            return response([
                'status' => true,
                'data' => $borrowedItems,
                'method' => "GET"
            ], 200);
        } catch (\Exception $e) {
            return response([
                'status' => false,
                'message' => 'An error occurred while fetching dates.',
                'error' => $e->getMessage(),
                'method' => 'GET',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(BorrowedItem $borrowedItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BorrowedItem $borrowedItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BorrowedItem $borrowedItem)
    {
        //
    }
}
