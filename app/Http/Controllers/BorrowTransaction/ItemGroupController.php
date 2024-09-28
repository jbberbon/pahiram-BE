<?php

namespace App\Http\Controllers\BorrowTransaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\BorrowTransaction\BookedDatesRequest;
use App\Http\Requests\BorrowTransaction\GetItemGroupByOfficeRequest;
use App\Http\Requests\ManageInventory\GetItemRequest;
use App\Http\Resources\ItemGroup\ItemGroupResourceForBorrowers;
use App\Http\Resources\ItemGroupBasedOnOfficeCollection;
use App\Http\Resources\ItemGroupBasedOnOfficeResource;
use App\Models\BorrowedItem;
use App\Models\BorrowedItemStatus;
use App\Models\Item;
use App\Models\ItemGroup;
use App\Models\ItemStatus;
use App\Services\ItemAvailability;
use App\Utils\DateUtil;
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
    private $itemAvailability;
    public function __construct()
    {
        // Item status
        $this->activeItemStatus = ItemStatus::where('item_status', "ACTIVE")->first();

        $this->pendingStatus = BorrowedItemStatus::where('borrowed_item_status', "PENDING_APPROVAL")->first();
        $this->approvedStatus = BorrowedItemStatus::where('borrowed_item_status', "APPROVED")->first();
        $this->inPossessionStatus = BorrowedItemStatus::where('borrowed_item_status', "IN_POSSESSION")->first();
        $this->itemAvailability = new ItemAvailability();

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
            // Get Actual Active Item Count
            $actualActiveItemCount = Item::getActiveItemStautCountExceptOverdueItems(itemGroupId: $itemGroupId);

            // 01. Get the sorted booked dates of the queried item_group (Sorted ascending by start date)
            $borrowedItems = BorrowedItem::join('items', 'borrowed_items.item_id', '=', 'items.id')
                ->join('item_groups', 'items.item_group_id', '=', 'item_groups.id')
                ->where('item_groups.id', $itemGroupId)
                // Only get the non-overdue items
                ->where('due_date', '>', now())
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
                ->orderBy('borrowed_items.start_date', 'asc')
                ->get()
                ->toArray();

            $mergedDates = DateUtil::mergeOverlappingDate($borrowedItems, $actualActiveItemCount);

            return response([
                'status' => true,
                'data' => [
                    'item_group_data' => [
                        // 'item_model' => $itemGroup->model_name,
                        'active_items' => $actualActiveItemCount,
                    ],
                    'dates' => $mergedDates
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
    public function show(GetItemRequest $request)
    {
        // Access the validated data
        $validatedData = $request->validated();

        // Now you can use the validated data
        $itemGroupId = $validatedData['item_group_id'];

        // Example: find the item group by ID
        $itemGroup = ItemGroup::find($itemGroupId);

        $itemGroupResource = new ItemGroupResourceForBorrowers($itemGroup);

        if (!$itemGroup) {
            return response()->json([
                'status' => false,
                'message' => 'Item group not found',
                'method' => 'GET'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $itemGroupResource,
            'method' => 'GET'
        ], 200);
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
