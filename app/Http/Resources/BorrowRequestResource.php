<?php

namespace App\Http\Resources;

use App\Models\BorrowPurpose;
use App\Models\BorrowTransactionStatus;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BorrowRequestResource extends JsonResource
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
            // 'borrower_id' => $this->borrower_id,
            'endorsed_by' => User::getNameBasedOnId($this->endorsed_by),
            'department' => Department::getDepartmentBasedOnId($this->department_id),
            'transac_status' => BorrowTransactionStatus::getStatusCodeById($this->transac_status_id),
            'purpose' => BorrowPurpose::getPurposeCodeById($this->purpose_id),
            'user_defined_purpose' => $this->user_defined_purpose,
            'penalty' => $this->penalty,
            'remarks_by_endorser' => $this->remarks_by_endorser,
            'remarks_by_approver' => $this->remarks_by_approver,
        ];
    }
}
