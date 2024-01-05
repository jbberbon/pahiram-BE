<?php

namespace App\Http\Requests\BorrowTransaction;

use App\Exceptions\RequestExtraPayloadMsg;
use App\Exceptions\RequestValidationFailedMsg;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class GetItemGroupByOfficeRequest extends FormRequest
{
    private $errorCode = 422;
    private $pending = null;
    public function rules(): array
    {
        return [
            'officeCode' => [
                'required',
                'exists:departments,department_code'
            ],

        ];
    }
    public function all($keys = null)
    {
        $data = parent::all($keys);
        $data['officeCode'] = $this->route('officeCode');

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
        $message = "Failed to retrieve items";
        $errorCode = $this->errorCode;
        RequestValidationFailedMsg::errorResponse($validator, $message, $errorCode);
    }
}
