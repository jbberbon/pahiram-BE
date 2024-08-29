<?php

namespace App\Http\Controllers\ItemInventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\ManageInventory\GetItemRequest;
use App\Http\Resources\Inventory\ItemInventoryCollection;
use App\Models\Item;
use App\Models\ItemGroup;
use Illuminate\Http\JsonResponse;

class ItemInventoryController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            // Fetch paginated item groups (adjust per_page as needed)
            $itemGroups = ItemGroup::paginate(21);

            // Wrap items in ItemInventoryCollection to format data and include pagination
            $itemGroupCollection = new ItemInventoryCollection($itemGroups);

            // Return successful JSON response
            return response()->json([
                'status' => true,
                'data' => $itemGroupCollection,
                'method' => 'GET',
            ], 200);

        } catch (\Exception $e) {
            // Handle exceptions and return error response
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching item groups.',
                'error' => $e->getMessage(),
                'method' => 'GET',
            ], 500);
        }
    }
    

    public function show(GetItemRequest $request)
    {
        $validatedData = $request->validated();
        
        return $validatedData;
    }
}
