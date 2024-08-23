<?php

namespace App\Http\Controllers\ItemInventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\ManageInventory\GetItemRequest;
use App\Http\Resources\Inventory\ItemInventoryCollection;
use App\Models\Item;
use Illuminate\Http\JsonResponse;

class ItemInventoryController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            // Fetch paginated items (adjust per_page as needed)
            $items = Item::paginate(5);

            // Wrap items in ItemInventoryCollection to format data and include pagination
            $itemCollection = new ItemInventoryCollection($items);

            // Return successful JSON response
            return response()->json([
                'status' => true,
                'data' => $itemCollection,
                'method' => 'GET',
            ], 200);

        } catch (\Exception $e) {
            // Handle exceptions and return error response
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching items.',
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
