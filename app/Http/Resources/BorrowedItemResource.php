<?php

namespace App\Http\Resources;

use App\Models\BorrowedItemStatus;
use App\Models\BorrowPurpose;
use App\Models\BorrowTransactionStatus;
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
            'approver' => User::getNameBasedOnId($this->approver_id),
            'status' => BorrowedItemStatus::getStatusById($this->borrowed_item_status_id),
            'start_date' => $this->start_date,
            'return_date' => $this->due_date,
        ];
    }
}
