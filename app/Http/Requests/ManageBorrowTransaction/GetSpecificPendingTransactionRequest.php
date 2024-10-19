<?php

namespace App\Http\Requests\ManageBorrowTransaction;

use App\Exceptions\RequestExtraPayloadMsg;
use App\Exceptions\RequestValidationFailedMsg;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GetSpecificPendingTransactionRequest extends FormRequest
{
    private $errorCode = 422;
    public function rules(): array
    {
        return [
            'transactionId' => [
                'required',
                'exists:borrow_transactions,id',
                // new IsTransactionPendingBorrowApprovalStatus
            ],
            'view_individual_items' => [
                'sometimes',
                // 'bool',
                Rule::in([true]),

            ]
        ];
    }
    public function all($keys = null)
    {
        $data = parent::all($keys);
        $data['transactionId'] = $this->route('transactionId');

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
        $message = "Failed to retrieve borrow request";
        $errorCode = $this->errorCode;
        RequestValidationFailedMsg::errorResponse($validator, $message, $errorCode);
    }
}
