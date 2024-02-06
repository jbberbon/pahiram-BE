<?php

namespace App\Http\Requests\ManageEndorsement;

use App\Exceptions\RequestExtraPayloadMsg;
use App\Exceptions\RequestValidationFailedMsg;
use App\Http\Middleware\IsEmployee;
use App\Rules\CancelTransacRule;
use App\Rules\EndorsementRules\IsPendingEndorserApproval;
use App\Rules\EndorsementRules\IsTransactionEndorser;
use App\Rules\IsEmployeeEmail;
use App\Rules\TransactionBelongsToUser;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class EndorsementApprovalRequest extends FormRequest
{
    private $errorCode = 422;
    public function rules(): array
    {
        return [
            'transactionId' => [
                'required',
                'exists:borrow_transactions,id',
                new IsEmployeeEmail,
                new IsTransactionEndorser,
                new IsPendingEndorserApproval
            ],
            'approval' => [
                'required',
                'boolean'
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
        $message = "Failed to update transaction";
        $errorCode = $this->errorCode;
        RequestValidationFailedMsg::errorResponse($validator, $message, $errorCode);
    }
}
