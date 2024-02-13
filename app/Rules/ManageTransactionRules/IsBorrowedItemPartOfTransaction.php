<?php

namespace App\Rules\ManageTransactionRules;

use App\Models\BorrowedItem;
use Illuminate\Contracts\Validation\Rule;

class IsBorrowedItemPartOfTransaction implements Rule
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }
    public function passes($attribute, $value)
    {
        $borrowedItem = BorrowedItem::find($value);

        if(!$borrowedItem) {
            return false;
        }

        if ($borrowedItem->borrowing_transac_id !== $this->request['transactionId']) {
            return false;
        }

        return true;
    }

    public function message()
    {
        return 'Item does not belong to transaction';
    }
}