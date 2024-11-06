<?php

namespace App\Rules\ManageTransactionRules;

use App\Models\BorrowedItem;
use App\Models\BorrowedItemStatus;
use App\Utils\Constants\Statuses\BORROWED_ITEM_STATUS;
use Illuminate\Contracts\Validation\Rule;

class IsItemApproved implements Rule
{

    private $approvedStatusId;
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
        $this->approvedStatusId = BorrowedItemStatus::getIdByStatus(BORROWED_ITEM_STATUS::APPROVED);
    }
    public function passes($attribute, $value)
    {
        $borrowedItem = BorrowedItem::find($value);

        if (!$borrowedItem) {
            return false;
        }

        return $borrowedItem->borrowed_item_status_id === $this->approvedStatusId;

    }

    public function message()
    {
        return "Item status is not approved.";
    }
}