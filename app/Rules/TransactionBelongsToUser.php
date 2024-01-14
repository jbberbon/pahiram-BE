<?php
namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Models\BorrowTransaction; // Adjust the model namespace as needed

class TransactionBelongsToUser implements Rule
{
    public function passes($attribute, $value)
    {
        $user = Auth::user(); // Assuming you're using the default User model

        return BorrowTransaction::where('id', $value)
            ->where('borrower_id', $user->id)
            ->exists();
    }

    public function message()
    {
        return 'The selected :attribute does not belong to the authenticated user.';
    }
}
