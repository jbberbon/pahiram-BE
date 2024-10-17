<?php

namespace App\Rules\ManageTransactionRules;

use App\Models\BorrowTransaction;
use App\Models\BorrowTransactionStatus;
use App\Models\SystemAdmin;
use App\Models\UserDepartment;
use App\Utils\Constants\Statuses\TRANSAC_STATUS;
use Auth;
use Illuminate\Contracts\Validation\Rule;

class IsAuthorizedToViewTransaction implements Rule
{
    public function passes($attribute, $value): bool
    {
        $user = Auth::user();
        $transaction = BorrowTransaction::find($value);

        if (!$transaction) {
            return false;
        }

        $isOwner = $transaction->borrower_id === $user->id;
        $isEndorser = $transaction->endorsed_by === $user->id;
        $isAdmin = SystemAdmin::where('user_id', $user->id)->exists();
        $isEmployeeOfTransactedOffice = UserDepartment::where('user_id', $user->id)
            ->where('department_id', $transaction->department_id)
            ->exists();

        return $isOwner || $isEndorser || $isAdmin || $isEmployeeOfTransactedOffice;
    }

    public function message(): string
    {
        return 'Unauthorized to view resource.';
    }
}