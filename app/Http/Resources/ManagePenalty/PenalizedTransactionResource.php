<?php

namespace App\Http\Resources\ManagePenalty;

use App\Models\BorrowedItem;
use App\Models\BorrowedItemStatus;
use App\Models\BorrowPurpose;
use App\Models\BorrowTransaction;
use App\Models\BorrowTransactionStatus;
use App\Models\Department;
use App\Models\Item;
use App\Models\PenalizedTransactionStatuses;
use App\Models\User;
use App\Utils\FormatMonetaryValues;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PenalizedTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $transaction = BorrowTransaction::find($this->borrowing_transac_id);
        return [
            'id' => $this->id,
            // 'borrowing_transac_id' => $this->borrowing_transac_id,
            'borrower' => User::getNameBasedOnId($transaction->borrower_id),
            'borrower_apc_id' => User::getApcIdBasedOnId($transaction->borrower_id),
            'status' => PenalizedTransactionStatuses::getStatusById($this->status_id),
            'office' => Department::getAcronymById($transaction->department_id),
            'penalty_amount' => FormatMonetaryValues::formatValue($transaction->penalty),
            'payment_facilitated_by' => User::getNameBasedOnId($this->payment_facilitated_by),
            'settlement_facilitated_by' => User::getNameBasedOnId($this->settlement_facilitated_by),
            'remarks_by_cashier' => $this->remarks_by_cashier,
            'remarks_by_supervisor' => $this->remarks_by_supervisor,
            'paid_at' => $this->paid_at,
            'settled_at' => $this->settled_at,
            'purpose' => BorrowPurpose::getPurposeById($transaction->purpose_id),
            'user_defined_purpose' => $transaction->user_defined_purpose,
            'created_at' => $transaction->created_at
        ];
    }
}
