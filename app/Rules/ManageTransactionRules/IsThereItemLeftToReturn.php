<?php

namespace App\Rules\ManageTransactionRules;

use App\Models\BorrowedItem;
use App\Models\BorrowedItemStatus;
use App\Models\BorrowTransaction;
use App\Models\BorrowTransactionStatus;
use App\Utils\Constants\Statuses\BORROWED_ITEM_STATUS;
use App\Utils\Constants\Statuses\TRANSAC_STATUS;
use Illuminate\Contracts\Validation\Rule;

class IsThereItemLeftToReturn implements Rule
{
    private $request;
    private $inpossessionStatusId;
    private $unreturnedStatusId;

    public function __construct($request)
    {
        $this->request = $request;
        $this->inpossessionStatusId = BorrowedItemStatus::getIdByStatus(BORROWED_ITEM_STATUS::IN_POSSESSION);
        $this->unreturnedStatusId = BorrowedItemStatus::getIdByStatus(BORROWED_ITEM_STATUS::UNRETURNED);

    }
    public function passes($attribute, $value)
    {
        $possessedItemCount = BorrowedItem::where('borrowing_transac_id', $this->request['transactionId'])
            ->where('borrowed_item_status_id', $this->inpossessionStatusId)
            ->count();

        $unreturnedItemCount = BorrowedItem::where('borrowing_transac_id', $this->request['transactionId'])
            ->where('borrowed_item_status_id', $this->unreturnedStatusId)
            ->count();

        if ($possessedItemCount === 0 && $unreturnedItemCount === 0) {
            return false;
        }

        return true;
    }

    public function message()
    {
        return 'No more items left to return';
    }
}