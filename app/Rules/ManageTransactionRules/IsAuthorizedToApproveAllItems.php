<?php

namespace App\Rules\ManageTransactionRules;

use App\Models\BorrowedItem;
use App\Models\BorrowedItemStatus;
use App\Models\BorrowTransaction;
use App\Models\BorrowTransactionStatus;
use App\Models\Role;
use App\Models\UserDepartment;
use App\Utils\Constants\Statuses\BORROWED_ITEM_STATUS;
use App\Utils\Constants\Statuses\TRANSAC_STATUS;
use App\Utils\Constants\USER_ROLE;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsAuthorizedToApproveAllItems implements Rule
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

        // Check if the user is a supervisor or co-supervisor
        $isSupervisor = $user->user_role_id === $this->supervisorId;
        $isCoSupervisor = $user->user_role_id === $this->coSupervisorId;

        // Supervisors or co-supervisors are authorized to approve all items
        if ($isSupervisor || $isCoSupervisor) {
            // \Log::info("Approve response", [$user]);
            return true;
        }

        // Check if the user is a lending employee
        $isLendingEmployee = $user->user_role_id === $this->lendingEmployeeId;

        if ($isLendingEmployee) {
            // Check if any items require supervisor approval
            $requiresSupervisorApproval = BorrowedItem::where('borrowing_transac_id', $this->request['transactionId'])
                ->where('borrowed_item_status_id', $this->pendingItemApprovalStatusId)
                ->join('items', 'items.id', '=', 'borrowed_items.item_id')
                ->join('item_groups', 'item_groups.id', '=', 'items.item_group_id')
                ->where('item_groups.is_required_supervisor_approval', true)
                ->exists();

            // Lending employees cannot approve if any items require supervisor approval
            return !$requiresSupervisorApproval;
        }

        // All other roles are unauthorized
        return false;
    }


    public function message()
    {
        return 'Insufficient privilege to approve transaction';
    }
}