<?php

namespace App\Http\Resources;

use App\Models\BorrowPurpose;
use App\Models\BorrowTransactionStatus;
use App\Models\Department;
use App\Models\User;
use App\Models\BorrowTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
        $departmentAcronym = Department::getAcronymById($this->department_id);
        $apcId = substr(User::find($this->borrower_id)->apc_id, -6);
        $createdAt = Carbon::parse($this->created_at);
        $formattedDate = $createdAt->format('mdy');
        $formattedTime = $createdAt->format('His');
        
        $customTransacId = "{$departmentAcronym}-{$apcId}-{$formattedDate}-{$formattedTime}";

        $resource = null;
        if (isset($this->endorsed_by)) {
            $resource = [
                'id' => $this->id,
                'endorsed_by' => [
                    'full_name' => User::getNameBasedOnId($this->endorsed_by),
                    'apc_id' => User::where('id', $this->endorsed_by)->first()->apc_id
                ],
                'custom_transac_id' => $customTransacId,
                'department_acronym' => $departmentAcronym,
                'transac_status' => BorrowTransactionStatus::getStatusById($this->transac_status_id),
                'purpose' => BorrowPurpose::getPurposeById($this->purpose_id),
                'user_defined_purpose' => $this->user_defined_purpose,
                'penalty' => $this->penalty,
                'remarks_by_endorser' => $this->remarks_by_endorser,
                'remarks_by_approver' => $this->remarks_by_approver,
                'created_at' => $createdAt,
                'updated_at' => $this->updated_at,
            ];
        } else {

            $resource = [
                'id' => $this->id,
                'custom_transac_id' => $customTransacId,
                'department_acronym' => $departmentAcronym,
                'transac_status' => BorrowTransactionStatus::getStatusById($this->transac_status_id),
                'purpose' => BorrowPurpose::getPurposeById($this->purpose_id),
                'user_defined_purpose' => $this->user_defined_purpose,
                'penalty' => $this->penalty,
                'remarks_by_endorser' => $this->remarks_by_endorser,
                'remarks_by_approver' => $this->remarks_by_approver,
                'created_at' => $createdAt,
                'updated_at' => $this->updated_at,
            ];
        }

        return $resource;
    }
}
