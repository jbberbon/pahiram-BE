<?php

namespace App\Http\Requests\BorrowTransaction;

use App\Exceptions\RequestExtraPayloadMsg;
use App\Exceptions\RequestValidationFailedMsg;
use App\Models\BorrowTransactionStatus;
use App\Rules\CancelTransacRule;
use App\Rules\TransactionBelongsToUser;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CancelBorrowRequest extends FormRequest
{
    private $errorCode = 422;
    public function rules(): array
    {
        return [
            'borrowRequest' => [
                'required',
                'exists:borrow_transactions,id',
                new TransactionBelongsToUser,
                new CancelTransacRule
            ],

        ];
    }
    public function all($keys = null)
    {
        $data = parent::all($keys);
        $data['borrowRequest'] = $this->route('borrowRequest');

        return $data;
    }
    protected function passedValidation()
    {
        $request = $this->input();
        $rules = $this->rules();
        $errorCode = $this->errorCode;
        RequestExtraPayloadMsg::errorResponse($request, $rules, $errorCode);
    }
    public function failedValidation(Validator $validator)
    {
        $message = "Failed to cancel borrowing request";
        $errorCode = $this->errorCode;
        RequestValidationFailedMsg::errorResponse($validator, $message, $errorCode);
    }
}
