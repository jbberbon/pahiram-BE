<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\BorrowTransaction;

class RequestBelongsToUser implements Rule
{
    public function passes($attribute, $value)
    {
        $user = auth()->user();

        // True OR False
        return BorrowTransaction::where('id', $value)
            ->where('borrower_id', $user->id)
            ->exists();
    }

    public function message()
    {
        return 'The borrow request does not belong to the authenticated user.';
    }
}
