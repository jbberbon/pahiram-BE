<?php

namespace App\Http\Resources\ManagePenalty\LendingOffice;

use App\Models\BorrowPurpose;
use App\Models\BorrowTransactionStatus;
use App\Models\User;
use App\Models\Department;
use App\Models\BorrowedItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
        \Log::info('ENDORSER', $request->toArray());
        $departmentAcronym = Department::getAcronymById($this->department_id);
        $borrower = User::find($this->borrower_id);
        $borrowerApcId = substr($borrower->apc_id, -6);

        $createdAt = Carbon::parse($this->created_at);
        $formattedDate = $createdAt->format('mdy');
        $formattedTime = $createdAt->format('His');

        $customTransacId = "{$departmentAcronym}-{$borrowerApcId}-{$formattedDate}-{$formattedTime}";

        // Fetch and restructure the borrowed items
        $items = BorrowedItem::where('borrowing_transac_id', $this->id)
            ->join('items', 'borrowed_items.item_id', '=', 'items.id')
            ->join('item_groups', 'items.item_group_id', '=', 'item_groups.id')
            ->join( // Use leftJoin to allow for null statuses
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
            ]);
        }

        $response = [
            'id' => $this->id,
            'borrower' => $borrower->first_name . ' ' . $borrower->last_name,
            'apc_id' => $borrowerApcId,
            'custom_transac_id' => $customTransacId,
            'borrow_transaction_status' => BorrowTransactionStatus::getStatusById($this->transac_status_id),
            'penalized_transaction_status' => $this->penalized_transaction_status,
            'purpose' => BorrowPurpose::getPurposeById($this->purpose_id),
            'user_defined_purpose' => $this->user_defined_purpose,
            'remarks_by_return_facilitator' => $this->remarks_by_return_facilitator,
            'total_penalty' => $this->penalty,
            'created_at' => $this->created_at,
            'items' => $restructuredItems,
        ];


        if (isset($this->endorsed_by)) {
            // Include endorsed_by only if it's set
            $response['endorsed_by'] = [
                'apc_id' => User::getApcIdBasedOnId($this->endorsed_by),
                'full_name' => User::getNameBasedOnId($this->endorsed_by),
            ];
        }

        return $response;
    }
}

