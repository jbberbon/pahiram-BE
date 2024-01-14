<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\BorrowTransactionStatus;

class CancelTransacRule implements Rule
{
    public function passes($attribute, $value)
    {
        $pending = BorrowTransactionStatus::where('transac_status_code', 1010)->first();
        $approved = BorrowTransactionStatus::where('transac_status_code', 2020)->first();

        return \DB::table('borrow_transactions')
            ->where('id', $value)
            ->where(function ($query) use ($pending, $approved) {
                $query->where('transac_status_id', $pending->id)
                    ->orWhere('transac_status_id', $approved->id);
            })
            ->exists();
    }

    public function message()
    {
        return 'The selected :attribute is invalid.';
    }
}
