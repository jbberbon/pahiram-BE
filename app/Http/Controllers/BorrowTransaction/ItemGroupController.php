<?php

namespace App\Http\Controllers\BorrowTransaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\BorrowTransaction\BookedDatesRequest;
use App\Http\Requests\BorrowTransaction\GetItemGroupByOfficeRequest;
use App\Http\Resources\ItemGroupBasedOnOfficeCollection;
use App\Http\Resources\ItemGroupBasedOnOfficeResource;
use App\Models\BorrowedItem;
use App\Models\BorrowedItemStatus;
use App\Models\Department;
use App\Models\Item;
use App\Models\ItemGroup;
use App\Models\ItemStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ItemGroupController extends Controller
{
    // Item inventory Status
    private $activeItemStatus;

    // Borrowed Item Statuses
    private $pendingStatus;
    private $inPossessionStatus;
    private $approvedStatus;
    // private $overdueReturnStatus;
    public function __construct()
    {
        // Item status
        $this->activeItemStatus = ItemStatus::where('item_status', "ACTIVE")->first();

        $this->pendingStatus = BorrowedItemStatus::where('borrowed_item_status', "PENDING_APPROVAL")->first();
        $this->approvedStatus = BorrowedItemStatus::where('borrowed_item_status', "APPROVED")->first();
        $this->inPossessionStatus = BorrowedItemStatus::where('borrowed_item_status', "IN_POSSESSION")->first();
        // $this->overdueReturnStatus = BorrowedItemStatus::where('borrowed_item_status', "OVERDUE_RETURN")->first();

    }
    /**
     * Search ItemGroup according to office
     */
    public function index(GetItemGroupByOfficeRequest $request)
    {
        $validatedData = $request->validated();
        $itemGroups = ItemGroup::where('department_id', function ($query) use ($validatedData) {
            $query->select('id')
                ->from('departments')
                ->where('department_acronym', $validatedData['departmentAcronym']);
        })->get();

        return response([
            'status' => true,
            'data' => new ItemGroupBasedOnOfficeCollection(ItemGroupBasedOnOfficeResource::collection($itemGroups)),
            'method' => 'GET',
        ], 200);

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
            // 01. Get the booked dates of the item_group
            $borrowedItems = BorrowedItem::join('items', 'borrowed_items.item_id', '=', 'items.id')
                ->join('item_groups', 'items.item_group_id', '=', 'item_groups.id')
                ->where('item_groups.id', $itemGroupId)
                ->where(function ($query) {
                    $query->where('borrowed_items.borrowed_item_status_id', $this->pendingStatus->id)
                        ->orWhere('borrowed_items.borrowed_item_status_id', $this->approvedStatus->id)
                        ->orWhere('borrowed_items.borrowed_item_status_id', $this->inPossessionStatus->id);
                })
                ->groupBy('borrowed_items.start_date', 'borrowed_items.due_date')
                ->select(
                    'borrowed_items.start_date as start',
                    'borrowed_items.due_date as end',
                    \DB::raw('COUNT(*) as count')
                )
                ->get();

            // return $borrowedItems;

            // 02. Get the count of the item with active status (ITEMS tb)
            $activeItemCount = Item::where('item_group_id', $itemGroupId)
                ->where('item_status_id', $this->activeItemStatus->id)
                ->get()
                ->count();

            // return $activeItemCount;

            // 03. Get count of overdue status (BORROWED ITEMS tb)
            $overdueCount = BorrowedItem::join('items', 'borrowed_items.item_id', '=', 'items.id')
                ->join('item_groups', 'items.item_group_id', '=', 'item_groups.id')
                ->where('item_groups.id', $itemGroupId)
                // Where status is in-possession
                ->where('borrowed_items.borrowed_item_status_id', $this->inPossessionStatus->id)
                //where now() > due date
                ->where('due_date', '<', now())
                ->get()
                ->count();


            $actualActiveItemCount = $activeItemCount - $overdueCount;

            $borrowedItems = $borrowedItems->map(function ($item) use ($actualActiveItemCount) {
                // 04. Format the dates to the expected format by the frontend
                $item['start'] = Carbon::parse($item['start'])->format('Y-m-d\TH:i');
                $item['end'] = Carbon::parse($item['end'])->format('Y-m-d\TH:i');

                // 05. Add Title Field for REACT FullCalendar display.
                // This will display how many items are available within the current booked Dates
                if ($actualActiveItemCount > $item['count']) {
                    $item['title'] = "Reserved quantity: " . $item['count'];
                } else {
                    $item['title'] = "Item slot fully booked";
                    $item['color'] = "#f44336";
                }
                return $item;
            });

            // 05. Get the name of the item group
            $itemGroup = ItemGroup::where('id', $itemGroupId)->first();

            return response([
                'status' => true,
                'data' => [
                    'item_model' => $itemGroup->model_name,
                    'active_items' => $actualActiveItemCount, // Overdue shouldnt be booked
                    'dates' => $borrowedItems
                ],
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
