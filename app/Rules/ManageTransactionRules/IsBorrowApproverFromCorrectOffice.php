<?php

namespace App\Rules\ManageTransactionRules;

use App\Models\BorrowTransaction;
use App\Models\BorrowTransactionStatus;
use App\Models\SystemAdmin;
use App\Models\UserDepartment;
use App\Utils\Constants\Statuses\TRANSAC_STATUS;
use Auth;
use Illuminate\Contracts\Validation\Rule;

class IsBorrowApproverFromCorrectOffice implements Rule
{
    // protected $request;

    // public function __construct($request)
    // {
    //     $this->request = $request;
    // }
    public function passes($attribute, $value): bool
    {
        $user = Auth::user();

        // Check if transaction is from same office the user is designated
        $transaction = BorrowTransaction::where('id', $value)->first();
        $userDepartment = UserDepartment::where('user_id', $user->id)->first();

        if (!$userDepartment) {
            return false;
        }

        if ($transaction->department_id !== $userDepartment->department_id) {
            return false;
        }

        return true;
    }

    public function message(): string
    {
        return 'User designation does not match the transaction data';
    }
}