<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\ManageInventory\GetItemRequest;
use App\Http\Resources\Inventory\ItemCollection;
use App\Models\Item;

class PLOManageInventory extends Controller
{
    public function index()
    {
        try {
            $items = Item::all();

            return response([
                'status' => true,
                'data' => new ItemCollection($items),
                'method' => "GET"
            ], 200);

        } catch (\Exception $e) {
            return response([
                'status' => false,
                'message' => 'An error occurred while fetching items',
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