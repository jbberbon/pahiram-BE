<?php

namespace App\Http\Resources;

use App\Models\BorrowPurpose;
use App\Models\BorrowTransactionStatus;
use App\Models\Department;
use App\Models\PenalizedTransaction;
use App\Models\PenalizedTransactionStatuses;
use App\Models\User;
use App\Models\BorrowedItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
        $departmentAcronym = Department::getAcronymById($this->department_id);
        $apcId = substr(User::find($this->borrower_id)->apc_id, -6);
        $createdAt = Carbon::parse($this->created_at);
        $formattedDate = $createdAt->format('mdy');
        $formattedTime = $createdAt->format('His');

        $customTransacId = "{$departmentAcronym}-{$apcId}-{$formattedDate}-{$formattedTime}";

        // Fetch and restructure the borrowed items
        $items = BorrowedItem::where('borrowing_transac_id', $this->id)
            ->join('items', 'borrowed_items.item_id', '=', 'items.id')
            ->join('item_groups', 'items.item_group_id', '=', 'item_groups.id')
            ->join(
                'borrowed_item_statuses',
                'borrowed_items.borrowed_item_status_id',
                '=',
                'borrowed_item_statuses.id'
            )
            ->select(
                'item_groups.model_name',
                'borrowed_items.id as borrowed_item_id',
                'borrowed_items.start_date',
                'borrowed_items.due_date',
                'borrowed_item_statuses.borrowed_item_status',
                'borrowed_item_statuses.id as borrowed_item_status_id'
            )
            ->get();

        // Group the items by model_name
        $groupedItems = $items->groupBy('model_name');
        $restructuredItems = collect();

        // Restructure the grouped items
        foreach ($groupedItems as $modelName => $groupedItem) {
            $item = $groupedItem->first(); // Assuming all items in the group share the same dates
            $restructuredItems->push([
                'model_name' => $modelName,
                'quantity' => $groupedItem->count(),
                'start_date' => Carbon::parse($item->start_date)->format('Y-m-d H:i:s'),
                'due_date' => Carbon::parse($item->due_date)->format('Y-m-d H:i:s'),
                'details' => $groupedItem->map(function ($item) use ($apcId) {
                    return [
                        'borrowed_item_id' => $item->borrowed_item_id,
                        'borrowed_item_status' => $item->borrowed_item_status ?? 'Unknown',
                        'apc_id' => User::find($this->borrower_id)->apc_id,
                    ];
                })
            ]);
        }

        // Base response structure
        if (isset($this->endorsed_by)) {
            $response = [
                'id' => $this->id,
                'borrower' => User::getNameBasedOnId($this->borrower_id),
                'endorsed_by' => [
                    'full_name' => User::getNameBasedOnId($this->endorsed_by),
                ],
                'apc_id' => User::find($this->borrower_id)->apc_id,
                'custom_transac_id' => $customTransacId,
                'status' => BorrowTransactionStatus::getStatusById($this->transac_status_id),
                'purpose' => BorrowPurpose::getPurposeById($this->purpose_id),
                'user_defined_purpose' => $this->user_defined_purpose,
                'created_at' => $this->created_at,
                'items' => $restructuredItems,
            ];
        } else {
            $response = [
                'id' => $this->id,
                'borrower' => User::getNameBasedOnId($this->borrower_id),
                'apc_id' => User::find($this->borrower_id)->apc_id,
                'custom_transac_id' => $customTransacId,
                'status' => BorrowTransactionStatus::getStatusById($this->transac_status_id),
                'purpose' => BorrowPurpose::getPurposeById($this->purpose_id),
                'user_defined_purpose' => $this->user_defined_purpose,
                'created_at' => $this->created_at,
                'items' => $restructuredItems,
            ];
        }

        // Add additional fields if needed
        if ($this->is_required_supervisor_approval !== null) {
            $response['is_required_supervisor_approval'] = $this->is_required_supervisor_approval;
        }

        if ($this->isApprovalOverdue !== null) {
            $response['is_approval_overdue'] = $this->isApprovalOverdue;
        }

        // Add penalty information if penalized
        $penalized = PenalizedTransaction::where('borrowing_transac_id', $this->id)->first();
        if ($penalized) {
            $response['penalized_transac_id'] = $penalized->id;
            $response['receipt_number'] = $penalized->receipt_number;
            $response['penalty_status'] = PenalizedTransactionStatuses::getStatusById($penalized->status_id);
            $response['remarks_by_cashier'] = $penalized->remarks_by_cashier;
            $response['remarks_by_finance_supervisor'] = $penalized->remarks_by_supervisor;
            $response['paid_at'] = $penalized->paid_at;
            $response['settled_at'] = $penalized->settled_at;
        }

        return $response;
    }

}