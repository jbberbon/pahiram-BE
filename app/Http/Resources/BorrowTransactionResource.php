<?php

namespace App\Http\Resources;

use App\Models\BorrowPurpose;
use App\Models\BorrowTransactionStatus;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BorrowTransactionResource extends JsonResource
{
    public $is_required_supervisor_approval;
    public $isApprovalOverdue;

    public function __construct($resource, $is_required_supervisor_approval = null, $isApprovalOverdue = null)
    {
        parent::__construct($resource);
        $this->is_required_supervisor_approval = $is_required_supervisor_approval;
        $this->isApprovalOverdue = $isApprovalOverdue;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'borrower' => User::getNameBasedOnId($this->borrower_id),
            'department' => Department::getDepartmentBasedOnId($this->department_id),
            'endorser' => User::getNameBasedOnId($this->endorsed_by),
            'purpose' => BorrowPurpose::getPurposeById($this->purpose_id),
            'user_defined_purpose' => $this->user_defined_purpose,
            'status' => BorrowTransactionStatus::getStatusById($this->transac_status_id),
            'penalty' => $this->penalty,
            'remarks_by_endorser' => $this->remarks_by_endorser,
            'remarks_by_approver' => $this->remarks_by_approver,
            'created_at' => $this->created_at
        ];

        if ($this->is_required_supervisor_approval !== null) {
            $data['is_required_supervisor_approval'] = $this->is_required_supervisor_approval;
        }

        if ($this->isApprovalOverdue !== null) {
            $data['is_approval_overdue'] = $this->isApprovalOverdue;
        }

        return $data;
    }
}
