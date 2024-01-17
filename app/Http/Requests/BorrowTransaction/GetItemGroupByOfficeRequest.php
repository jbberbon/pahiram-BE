<?php

namespace App\Http\Requests\BorrowTransaction;

use App\Exceptions\RequestExtraPayloadMsg;
use App\Exceptions\RequestValidationFailedMsg;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class GetItemGroupByOfficeRequest extends FormRequest
{
    private $errorCode = 422;
    public function rules(): array
    {
        return [
            'departmentAcronym' => [
                'required',
                'exists:departments,department_acronym'
            ],

        ];
    }
    public function all($keys = null)
    {
        $data = parent::all($keys);
        $data['departmentAcronym'] = $this->route('departmentAcronym');

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
