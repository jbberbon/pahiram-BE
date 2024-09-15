<?php

namespace App\Http\Resources\ItemGroup;
use App\Models\Department;
use App\Models\Item;
use App\Models\ItemGroupCategory;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class ItemGroupResourceForBorrowers extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'item_group_id' => $this->id,
            'model_name' => $this->model_name,
            'in_circulation' => Item::getActiveItemStautCountExceptOverdueItems($this->id),
            'group_category' => ItemGroupCategory::getCategoryNameById($this->group_category_id),
            'department' => Department::getAcronymById($this->department_id),
            'description' => $this->description
        ];
    }
}
