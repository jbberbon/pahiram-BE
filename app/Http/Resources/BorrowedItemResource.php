<?php

namespace App\Http\Resources;

use App\Models\BorrowedItemStatus;
use App\Models\BorrowPurpose;
use App\Models\BorrowTransactionStatus;
use App\Models\Item;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BorrowedItemResource extends JsonResource
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
            'apc_item_id' => Item::getApcIdByItemId($this->item_id),
            'model_name' => Item::getModelNameById($this->item_id),
            'status' => BorrowedItemStatus::getStatusById($this->borrowed_item_status_id),
            'start_date' => $this->start_date,
            'due_date' => $this->due_date,
        ];
    }
}
