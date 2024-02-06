<?php

namespace App\Http\Resources;

use App\Models\BorrowPurpose;
use App\Models\BorrowTransactionStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EndorsementResource extends JsonResource
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
            'borrower' => User::getNameBasedOnId($this->borrower_id),
            'status' => BorrowTransactionStatus::getStatusById($this->transac_status_id),
            'purpose' => BorrowPurpose::getPurposeById($this->purpose_id),
            'user_defined_purpose' => $this->user_defined_purpose,
            'created_at' => $this->created_at,
        ];
    }
}
