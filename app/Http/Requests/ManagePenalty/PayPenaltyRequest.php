<?php

namespace App\Http\Requests\ManagePenalty;

use App\Exceptions\RequestExtraPayloadMsg;
use App\Exceptions\RequestValidationFailedMsg;

use App\Rules\ManagePenaltyRules\IsPenalizedTransacPendingPaymentOrUnpaid;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class PayPenaltyRequest extends FormRequest
{
    private $errorCode = 422;
    public function rules(): array
    {
        return [
            'penalizedTransactionId' => [
                'required',
                'exists:penalized_transactions,id',
                new IsPenalizedTransacPendingPaymentOrUnpaid
            ],
            'receipt_number' => [
                'required',
                'string',
                'min:5',
                'max:30'
            ],
            'remarks' => [
                'sometimes',
                'string',
                'min:5',
                'max:30'
            ]
        ];
    }
    public function all($keys = null)
    {
        $data = parent::all($keys);
        $data['penalizedTransactionId'] = $this->route('penalizedTransactionId');

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
        $message = "Failed to mark transaction as paid";
        $errorCode = $this->errorCode;
        RequestValidationFailedMsg::errorResponse($validator, $message, $errorCode);
    }
}
