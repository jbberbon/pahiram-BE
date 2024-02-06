<?php

namespace App\Rules\EndorsementRules;

use App\Models\BorrowTransaction;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class IsTransactionEndorser implements Rule
{
    public function passes($attribute, $value)
    {
        // Check if the user is tagged in the endorser field of the transaction
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        $transaction = BorrowTransaction::where('id', $value)->first();
        if (!$transaction) {
            return false;
        }
        return $transaction->endorsed_by === $user->id;
    }

    public function message()
    {
        return 'Unauthorized access';
    }
}
