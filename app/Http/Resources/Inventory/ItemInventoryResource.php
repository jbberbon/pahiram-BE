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
            'is_required_supervisor_approval' => $this->is_required_supervisor_approval,
            'group_category_id' => ItemGroupCategory::getCategoryNameById($this->group_category_id),
            'department' => Department::getAcronymById($this->department_id),
        ];
    }
}

