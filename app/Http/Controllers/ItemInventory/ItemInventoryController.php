<?php

namespace App\Http\Controllers\ItemInventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\ManageInventory\GetItemRequest;
use App\Http\Resources\Inventory\ItemInventoryCollection;
use App\Models\Item;
use App\Models\ItemGroup;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ItemInventoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            // Retrieve the search query and filters from the request
            $search = $request->input('model_name');
            $categoryName = $request->input('category_name');
            $departmentAcronym = $request->input('department_acronym');

            // Start a query on the ItemGroup model
            $query = ItemGroup::query();

            // Apply search filter by model_name
            if ($search) {
                $query->where('model_name', 'like', '%' . $search . '%');
            }

            // Apply category filter by category_name
            if ($categoryName) {
                // Join with the item_group_categories table
                $query->join('item_group_categories', 'item_groups.group_category_id', '=', 'item_group_categories.id')
                    ->where('item_group_categories.category_name', 'like', '%' . $categoryName . '%')
                    ->select('item_groups.*'); // Make sure to select columns from the ItemGroup model
            }

            // Apply department filter by acronym
            if ($departmentAcronym) {
                // Join with the department table
                $query->join('departments', 'item_groups.department_id', '=', 'departments.id')
                    ->where('departments.department_acronym', $departmentAcronym)
                    ->select('item_groups.*'); // Make sure to select columns from the ItemGroup model
            }

            // Fetch paginated item groups (adjust per_page as needed)
            $itemGroups = $query->paginate(21);

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
