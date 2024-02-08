<?php

namespace App\Http\Requests\BorrowTransaction;

use App\Rules\ExistsInDbOrApcis;
use App\Rules\HasEnoughActiveItems;
use App\Rules\UniqueItemGroupIds;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

use App\Exceptions\RequestExtraPayloadMsg;
use App\Exceptions\RequestValidationFailedMsg;
use Illuminate\Validation\Rule;

class SubmitBorrowRequest extends FormRequest
{
    private $errorCode = 422;
    public function rules(): array
    {
        return [
            'endorsed_by' => [
                'sometimes',
                'string',
                'min:5',
                'max:15',
                Rule::notIn([auth()->user()->apc_id,]),
                new ExistsInDbOrApcis,
            ],
            'apcis_token' => [
                'required_with:endorsed_by',
                'string',
                'regex:/^[a-zA-Z0-9|]+$/',
            ],
            'department' => [
                'required',
                'string',
                'min:2',
                'exists:departments,department_acronym'
            ],
            'purpose' => [
                'required',
                'string',
                'min:4',
                'exists:borrow_purposes,purpose'
            ],
            'user_defined_purpose' => [
                'required',
                'string',
                'regex:/^[a-zA-Z0-9\s|]+$/',
                'min:5',
                'max:50'
            ],

            /**
             * Borrowed Items ----------------------------------------------------
             */
            'items' => [
                'sometimes',
                'array',
                'min:1',
                'max:10',
                new UniqueItemGroupIds
            ],
            'items.*' => [
                'required',
                'array',
                'size:4',
                // Checks the count of the currently Active Status item in Items Table
                new HasEnoughActiveItems
            ],
            'items.*.item_group_id' => [
                'required',
                'string',
                'regex:/^[a-zA-Z0-9-]+$/',
                'exists:item_groups,id'
            ],
            'items.*.start_date' => [
                'required',
                'string',
                'regex:/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',
                'date_format:Y-m-d H:i:s',
                'after:' . now()->tz('Asia/Taipei')->format('Y-m-d H:i:s')
            ],
            'items.*.return_date' => [
                'required',
                'string',
                'regex:/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',
                'date_format:Y-m-d H:i:s',
                'after:items.*.start_date',
            ],
            'items.*.quantity' => [
                'required',
                'integer',
                'min: 1',
                'max:3'
            ]
        ];
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
        $message = "Failed to Submit Request";
        $errorCode = $this->errorCode;
        RequestValidationFailedMsg::errorResponse($validator, $message, $errorCode);
    }

}
