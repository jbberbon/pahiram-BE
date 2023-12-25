<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\BorrowTransaction;

class RequestBelongsToUser implements Rule
{
    public function passes($attribute, $value)
    {
        // Check if the borrowRequest ID belongs to the authenticated user
        $user = auth()->user();
        $belongsToUser = BorrowTransaction::where('id', $value)
            ->where('borrower_id', $user->id)
            ->exists();

        return $belongsToUser;
    }

    public function message()
    {
        return 'The borrow request does not belong to the authenticated user.';
    }
}
