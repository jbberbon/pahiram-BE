<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Http\Resources\Category\ItemCategoryCollection;
use App\Http\Requests\ManageItemCategory\ItemCategoryRequest; // Corrected namespace
use App\Models\ItemGroupCategory;
use Illuminate\Http\JsonResponse;

class ItemCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $itemCategories = ItemGroupCategory::paginate(10);

            $itemCategoryCollection = new ItemCategoryCollection($itemCategories);

            return response()->json([
                'status' => true,
                'data' => $itemCategoryCollection,
                'method' => 'GET',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching item categories.',
                'error' => $e->getMessage(),
                'method' => 'GET',
            ], 500);
        }
    }

    public function search(ItemCategoryRequest $request)
    {
        $search = $request->input('category_name');

        $query = ItemGroupCategory::query();

        if ($search) {
            $query->where('category_name', 'like', '%' . $search . '%');
        }

        $itemCategories = $query->paginate(5);

        $itemCategoryCollection = new ItemCategoryCollection($itemCategories);

        return response()->json([
            'status' => true,
            'data' => $itemCategoryCollection,
            'method' => 'GET',
        ], 200);
    }
}
