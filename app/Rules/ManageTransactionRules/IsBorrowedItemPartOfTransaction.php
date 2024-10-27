<?php

namespace App\Rules\ManageTransactionRules;

use App\Models\BorrowedItem;
use Illuminate\Contracts\Validation\Rule;

class IsBorrowedItemPartOfTransaction implements Rule
{
    protected $transacId;

    public function __construct($request)
    {
        $this->transacId = $request['transactionId'];
    }
    public function passes($attribute, $value)
    {
        return BorrowedItem::where('id', $value)
            ->where('borrowing_transac_id', $this->transacId)
            ->exists();
    }

    public function message()
    {
        return 'Item does not belong to transaction';
    }
}