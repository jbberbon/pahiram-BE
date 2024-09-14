<?php

namespace App\Http\Resources\Inventory;

use App\Models\Department;
use App\Models\ItemGroupCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemInventoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'model_name' => $this->model_name,
            'group_category' => ItemGroupCategory::getCategoryNameById($this->group_category_id),
            'department' => Department::getAcronymById($this->department_id),
            // 'item_group_image_endpoint' => $this->item_group_image_endpoint,
        ];
    }
}

