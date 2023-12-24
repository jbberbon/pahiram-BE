<?php

namespace App\Http\Requests\BorrowTransaction;

use App\Models\BorrowPurpose;
use App\Rules\ExistsInDepartment;
use App\Rules\ExistsInItemGroup;
use App\Rules\ExistsInPurpose;
use App\Rules\ExistsInUsers;
use App\Rules\HasActiveItems;
use App\Rules\UniqueIds;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

use App\Exceptions\RequestExtraPayloadMsg;
use App\Exceptions\RequestValidationFailedMsg;

class SubmitBorrowRequest extends FormRequest
{
    private $errorCode = 422;
    public function rules(): array
    {
        $purposeOther = BorrowPurpose::where('purpose_code', 7070)->first();
        return [
            'endorsed_by' => ['string', new ExistsInUsers],
            'department_id' => ['required', 'string', new ExistsInDepartment],
            'purpose_id' => ['required', 'string', new ExistsInPurpose],
            'user_defined_purpose' => ['required_if:purpose_id,' . $purposeOther->id, 'string'], 
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
                new HasActiveItems
            ],
            'items.*.item_group_id' => [
                'required', 
                'string', 
                new ExistsInItemGroup
            ],
            'items.*.start_date' => [
                'required', 
                'string', 
                'date_format:Y-m-d H:i:s',
                'after:' . now()->tz('Asia/Taipei')->format('Y-m-d H:i:s')
            ],
            'items.*.return_date' => [
                'required', 
                'string', 
                'date_format:Y-m-d H:i:s',
                'after:items.*.start_date',
                // 'date_add:30 minutes'
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
