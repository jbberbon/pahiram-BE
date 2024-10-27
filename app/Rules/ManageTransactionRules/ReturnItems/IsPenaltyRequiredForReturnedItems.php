<?php

namespace App\Rules\ManageTransactionRules\ReturnItems;

use App\Utils\Constants\Statuses\BORROWED_ITEM_STATUS;
use Illuminate\Contracts\Validation\Rule;

class IsPenaltyRequiredForReturnedItems implements Rule
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }
    public function passes($attribute, $value)
    {
        foreach ($this->request['items'] as $index => $item) {
            $itemStatus = $item['item_status'] ?? null;
            $penalizedStatuses = BORROWED_ITEM_STATUS::PENALIZED_STATUSES;

            // If item status requires a penalty, ensure penalty is provided and is not empty
            if (in_array($itemStatus, $penalizedStatuses) && empty($item['penalty'])) {
                return false; // Fail if penalty is required but missing or empty
            }
        }

        return true;
    }

    public function message()
    {
        return 'Penalty field is required';
    }
}