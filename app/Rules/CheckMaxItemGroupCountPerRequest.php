<?php

namespace App\Rules;

use App\Models\BorrowedItem;
use App\Models\BorrowedItemStatus;
use App\Utils\Constants\BorrowedItemStatusConst;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckMaxItemGroupCountPerRequest implements Rule
{
    protected $requestData;
    protected $maxCount;
    protected $pendingApprovalCode;
    protected $pendingApprovalId;

    public function __construct($requestData)
    {
        $this->requestData = $requestData;
        $this->maxCount = 10;
        $this->pendingApprovalCode = BorrowedItemStatusConst::PENDING;
        $this->pendingApprovalId = BorrowedItemStatus::where('borrowed_item_status_code', $this->pendingApprovalCode)->first()->id;

    }
    public function passes($attribute, $value)
    {
        /**
         *  LOGIC!!
         *  01. Count the # of item_groups within a "specific" borrow transaction 
         *      01.1 Only borrowed_items with Pending status are included
         *  02. Count the JSON body's edit_existing_items for item_groups that are being CANCELLED
         *  03. Count the JSON body's add_new_items
         *  04. Subtract 02. Cancelled Existing Items From 01. Pending Item Groups
         *  05. Add 02 and 03 to get the transaction's count of item groups
         *  06. It should reach the maximum amount of 10 item_groups
         */

        // 01. Count the # of item_groups within a "specific" borrow transaction
        $transacPendingGroupCount = DB::table('borrowed_items')
            ->where('borrowing_transac_id', $this->requestData['requestId'])
            ->where('borrowed_item_status_id', $this->pendingApprovalId)
            ->join('items', 'borrowed_items.item_id', '=', 'items.id')
            ->distinct()
            ->pluck('items.item_group_id')
            ->count();

        // 02. Count the JSON body's edit_existing_items for item_groups that are being CANCELLED
        $cancelledGroupCount = collect($this->requestData['edit_existing_items'])
            ->filter(function ($item) {
                return isset($item['is_cancelled']) && $item['is_cancelled'] === true;
            })->count();

        // 04. Subtract 02. Cancelled Existing Items From 01. Pending Item Groups
        $remainingPendingGroupsCount = null;
        if ($transacPendingGroupCount === 0) {
            // If there are no pending groups, ensure cancelledGroupCount is also 0 (BUSINESS LOGIC. You cant edit transactions that arent pendingApproval)
            $cancelledGroupCount = 0;
        } else {
            // Continue with the regular logic for subtracting cancelled groups
            $remainingPendingGroupsCount = $transacPendingGroupCount - $cancelledGroupCount;
        }

        // 03. Count the JSON body's add_new_items
        $newItemCount = collect($this->requestData['add_new_items'])->count();

        // 05. Add 02 and 03 to get the transaction's count of item groups
        $total = $remainingPendingGroupsCount + $newItemCount;

        // 06. It should reach the maximum amount of 10 item groups
        return $total <= $this->maxCount;
    }

    public function message()
    {
        return "The total count of item groups cannot exceed " . $this->maxCount;
    }
}