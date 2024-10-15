<?php

// App/Http/Resources/EndorsementResource.php
namespace App\Http\Resources;

use App\Models\BorrowPurpose;
use App\Models\BorrowTransactionStatus;
use App\Models\User;
use App\Models\Department;
use App\Models\BorrowedItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
            ->select(
                'item_groups.model_name',
                'borrowed_items.id as borrowed_item_id',
                'borrowed_items.start_date',
                'borrowed_items.due_date'
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

        return [
            'id' => $this->id,
            'borrower' => User::getNameBasedOnId($this->borrower_id),
            'apc_id' => User::find($this->borrower_id)->apc_id,
            'custom_transac_id' => $customTransacId,
            'status' => BorrowTransactionStatus::getStatusById($this->transac_status_id),
            'purpose' => BorrowPurpose::getPurposeById($this->purpose_id),
            'user_defined_purpose' => $this->user_defined_purpose,
            'created_at' => $this->created_at,
            'items' => $restructuredItems, // Add the restructured items here with dates
        ];
    }
}
