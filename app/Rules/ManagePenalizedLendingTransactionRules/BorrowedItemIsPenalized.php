<?php

namespace App\Rules\ManagePenalizedLendingTransactionRules;

use App\Models\BorrowedItem;
use App\Services\RetrieveStatusService\BorrowedItemStatusService;
use Illuminate\Contracts\Validation\Rule;

class BorrowedItemIsPenalized implements Rule
{
    public function passes($attribute, $value)
    {
        // Instead of basing it on penalty amount
        // Let's base it on the Borrowed Item Status upon returned
        return BorrowedItem::where('id', $value)
            ->whereIn('borrowed_item_status_id', BorrowedItemStatusService::getPenalizedStatusIds())
            ->exists();
    }

    public function message()
    {
        return 'The item must have a penalized status.';
    }
}