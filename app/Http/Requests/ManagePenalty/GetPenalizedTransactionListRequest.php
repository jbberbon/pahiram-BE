<?php

namespace App\Http\Requests\ManagePenalty;

use App\Exceptions\RequestExtraPayloadMsg;
use App\Exceptions\RequestValidationFailedMsg;
use App\Rules\CancelTransacRule;
use App\Rules\EndorsementRules\IsPendingEndorserApproval;
use App\Rules\EndorsementRules\IsTransactionEndorser;
use App\Rules\IsEmployeeEmail;
use App\Rules\TransactionBelongsToUser;
use App\Utils\Constants\Statuses\PENALIZED_TRANSAC_STATUS;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GetPenalizedTransactionListRequest extends FormRequest
{
    private $errorCode = 422;
    public function rules(): array
    {
        return [
            'status' => [
                'sometimes',
                'string',
                Rule::in(PENALIZED_TRANSAC_STATUS::ALL)
            ]

        ];
    }
    // public function all($keys = null)
    // {
    //     $data = parent::all($keys);
    //     $data['transactionId'] = $this->route('transactionId');

    //     return $data;
    // }
    protected function passedValidation()
    {
        $request = $this->input();
        $rules = $this->rules();
        $errorCode = $this->errorCode;
        RequestExtraPayloadMsg::errorResponse($request, $rules, $errorCode);
    }
    public function failedValidation(Validator $validator)
    {
        $message = "Failed to get transactions";
        $errorCode = $this->errorCode;
        RequestValidationFailedMsg::errorResponse($validator, $message, $errorCode);
    }
}
