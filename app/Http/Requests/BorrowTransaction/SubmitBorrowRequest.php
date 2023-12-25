<?php

namespace App\Http\Requests\BorrowTransaction;

use App\Models\BorrowPurpose;
use App\Rules\ExistsInDbOrApcis;
use App\Rules\HasEnoughActiveItems;
use App\Rules\UniqueIds;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

use App\Exceptions\RequestExtraPayloadMsg;
use App\Exceptions\RequestValidationFailedMsg;
use App\Utils\Constants\BorrowPurposeConst;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SubmitBorrowRequest extends FormRequest
{
    private $otherPurposeCode = BorrowPurposeConst::OTHER;
    private $errorCode = 422;
    public function rules(): array
    {
        $purposeOther = BorrowPurpose::where('purpose_code', $this->otherPurposeCode)->first();
        return [
            'endorsed_by' => [
                'string',
                'min:6',
                'max:15',
                Rule::notIn([auth()->user()->apc_id,]),
                new ExistsInDbOrApcis,
            ],
            'apcis_token' => [
                'required_with:endorsed_by',
                'string',
                'regex:/^[a-zA-Z0-9|]+$/',
            ],
            'department_id' => [
                'required',
                'string',
                'regex:/^[a-zA-Z0-9-]+$/',
                'exists:departments,id'
            ],
            'purpose_id' => [
                'required',
                'string',
                'regex:/^[a-zA-Z0-9-]+$/',
                'exists:borrow_purposes,id'
            ],
            'user_defined_purpose' => [
                'required_if:purpose_id,' . $purposeOther->id,
                'string',
                'min:5',
                'max:50'
            ],
            'items' => [
                'required',
                'array',
                'min:1',
                'max:10',
                new UniqueIds
            ],
            'items.*' => [
                'required',
                'array',
                'size:4',
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
