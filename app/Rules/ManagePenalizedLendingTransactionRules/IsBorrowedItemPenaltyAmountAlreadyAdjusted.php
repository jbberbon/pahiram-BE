<?php

namespace App\Rules\ManagePenalizedLendingTransactionRules;

use App\Models\BorrowedItem;
use Illuminate\Contracts\Validation\Rule;

class IsBorrowedItemPenaltyAmountAlreadyAdjusted implements Rule
{
    public function passes($attribute, $value)
    {
        // Instead of basing it on penalty amount
        // Let's base it on the Borrowed Item Status upon returned
        return BorrowedItem::where('id', $value)
            ->whereNull('penalty_finalized_by')
            ->exists();
    }

    public function message()
    {
        return 'The item penalty amount was already adjusted.';
    }
}