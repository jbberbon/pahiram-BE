<?php

namespace App\Http\Resources\Inventory;

use App\Models\BorrowedItemStatus;
use App\Models\Item;
use App\Models\ItemGroup;
use App\Models\ItemStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
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
            'apc_item_id' => $this->apc_item_id,
            'model_name' => Item::getModelNameById($this->id),
            'status' => ItemStatus::getStatusById($this->item_status_id),
            'purchase_order_id' => $this->purchase_order_id,
            'office' => ItemGroup::getOfficeById($this->item_group_id),
            'designated_to' => User::getNameBasedOnId($this->designated_to),
            'unit_cost' => $this->unit_cost,
            'warranty_expiration' => $this->warranty_expiration
        ];
    }
}
