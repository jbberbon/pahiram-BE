<?php

namespace App\Rules\ManageTransactionRules;

use App\Models\BorrowedItem;
use App\Models\BorrowedItemStatus;
use App\Models\BorrowTransaction;
use App\Models\BorrowTransactionStatus;
use App\Models\Role;
use App\Utils\Constants\Statuses\BORROWED_ITEM_STATUS;
use App\Utils\Constants\Statuses\TRANSAC_STATUS;
use App\Utils\Constants\USER_ROLE;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsEmployeeAuthorizedToApproveAllItems implements Rule
{
    // private $pendingBorrowApprovalStatusId;
    private $pendingItemApprovalStatusId;
    private $supervisorId;
    private $coSupervisorId;

    private $lendingEmployeeId;
    private $inventoryManagerId;
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
        // $this->pendingBorrowApprovalStatusId = BorrowTransactionStatus::getIdByStatus(TRANSAC_STATUS::PENDING_BORROWING_APPROVAL);
        $this->pendingItemApprovalStatusId = BorrowedItemStatus::getIdByStatus(BORROWED_ITEM_STATUS::PENDING_APPROVAL);
        $this->supervisorId = Role::getIdByRole(USER_ROLE::SUPERVISOR);
        $this->coSupervisorId = Role::getIdByRole(USER_ROLE::COSUPERVISOR);
        $this->lendingEmployeeId = Role::getIdByRole(USER_ROLE::LENDING_EMPLOYEE);
        $this->inventoryManagerId = Role::getIdByRole(USER_ROLE::INVENTORY_MANAGER);
    }
    public function passes($attribute, $value)
    {
        $user = Auth::user();

        // If user is supervisor / co-supervisor of the designated office then, passed
        $isSupervisor = $user->user_role_id === $this->supervisorId;
        $isCoSupervisor = $user->user_role_id === $this->coSupervisorId;

        if ($isSupervisor || $isCoSupervisor) {
            return true;
        }

        // User is lending or inventory manager
        $lendingEmployeeId = $user->user_role_id === $this->lendingEmployeeId;

        if ($lendingEmployeeId) {
            $borrowedItems = BorrowedItem::where('borrowing_transac_id', $this->request['transactionId'])
                ->where('borrowed_item_status_id', $this->pendingItemApprovalStatusId)
                ->join('items', 'items.id', '=', 'borrowed_items.item_id')
                ->join('item_groups', 'item_groups.id', '=', 'items.item_group_id')
                ->select(
                    'borrowed_items.id as borrowed_item_id',
                    'item_groups.is_required_supervisor_approval'
                )
                ->get();

            \Log::error($borrowedItems);

            // Check if the items require supervisor approval
            foreach ($borrowedItems as $item) {
                if ($item->is_required_supervisor_approval) {
                    return false;
                }
            }

            if ($borrowedItems) {
                return true;
            }
        }

        // If other role 
        return false;

    }

    public function message()
    {
        return 'Insufficient privilege to approve transaction';
    }
}