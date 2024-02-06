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
        $resource = null;
        if (isset($this->endorsed_by)) {
            $resource = [
                'id' => $this->id,
                'endorsed_by' => [
                    'full_name' => User::getNameBasedOnId($this->endorsed_by),
                    'apc_id' => User::where('id', $this->endorsed_by)->first()->apc_id
                ],
                'department' => Department::getDepartmentBasedOnId($this->department_id),
                'department_acronym' => Department::getAcronymById($this->department_id),
                'transac_status' => BorrowTransactionStatus::getStatusById($this->transac_status_id),
                'purpose' => BorrowPurpose::getPurposeById($this->purpose_id),
                'user_defined_purpose' => $this->user_defined_purpose,
                'penalty' => $this->penalty,
                'remarks_by_endorser' => $this->remarks_by_endorser,
                'remarks_by_approver' => $this->remarks_by_approver,
                'created_at' => $this->created_at,
            ];
        } else {

            $resource = [
                'id' => $this->id,
                'department' => Department::getDepartmentBasedOnId($this->department_id),
                'department_acronym' => Department::getAcronymById($this->department_id),
                'transac_status' => BorrowTransactionStatus::getStatusById($this->transac_status_id),
                'purpose' => BorrowPurpose::getPurposeById($this->purpose_id),
                'user_defined_purpose' => $this->user_defined_purpose,
                'penalty' => $this->penalty,
                'remarks_by_endorser' => $this->remarks_by_endorser,
                'remarks_by_approver' => $this->remarks_by_approver,
                'created_at' => $this->created_at,
            ];
        }

        return $resource;
    }
}
