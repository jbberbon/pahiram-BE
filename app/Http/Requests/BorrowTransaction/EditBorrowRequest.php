<?php

namespace App\Http\Requests\BorrowTransaction;

use App\Models\BorrowPurpose;
use App\Rules\CheckMaxItemGroupCountPerRequest;
use App\Rules\ExistsInDbOrApcis;
use App\Rules\HasEnoughActiveItems;
use App\Rules\ItemGroupBelongsToBorrowedItems;
use App\Rules\ItemGroupDoesNotBelongToBorrowedItems;
use App\Rules\UniqueIds;
use App\Rules\UniqueIdsAcrossArrays;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

use App\Exceptions\RequestExtraPayloadMsg;
use App\Exceptions\RequestValidationFailedMsg;
use App\Utils\Constants\BorrowPurposeConst;
use Illuminate\Validation\Rule;
use App\Rules\ItemGroupShouldHavePendingStatus;

class EditBorrowRequest extends FormRequest
{
    private $otherPurposeCode = BorrowPurposeConst::OTHER;
    private $errorCode = 422;
    public function rules(): array
    {
        $purposeOther = BorrowPurpose::where('purpose_code', $this->otherPurposeCode)->first();
        return [
            'requestId' => [
                'required',
                'exists:borrow_transactions,id'
            ],
            /**
             * Request Data ----------------------------------------------------
             */
            'request_data' => [
                'array',
                'min:1',
                'max:5',
            ],
            'request_data.endorsed_by' => [
                'string',
                'min:6',
                'max:15',
                Rule::notIn([auth()->user()->apc_id,]),
                new ExistsInDbOrApcis,
            ],
            'request_data.apcis_token' => [
                'required_with:endorsed_by',
                'string',
                'regex:/^[a-zA-Z0-9|]+$/',
            ],
            'request_data.department_id' => [
                'string',
                'regex:/^[a-zA-Z0-9-]+$/',
                'exists:departments,id'
            ],
            'request_data.purpose_id' => [
                'string',
                'regex:/^[a-zA-Z0-9-]+$/',
                'exists:borrow_purposes,id'
            ],
            'request_data.user_defined_purpose' => [
                'required_if:purpose_id,' . $purposeOther->id,
                'string',
                'min:5',
                'max:50'
            ],
            /**
             * Edit Existing items ----------------------------------------------------
             */
            'edit_existing_items' => [
                'array',
                'min:1',
                'max:10',
                new UniqueIds
            ],
            'edit_existing_items.*' => [
                'required',
                'array',
                'min:2',
                // Checks the count of the currently Active Status item in Items Table
                new HasEnoughActiveItems
            ],
            'edit_existing_items.*.item_group_id' => [
                'required',
                'string',
                'regex:/^[a-zA-Z0-9-]+$/',
                'exists:item_groups,id',
                new ItemGroupBelongsToBorrowedItems,
                new ItemGroupShouldHavePendingStatus
            ],
            'edit_existing_items.*.start_date' => [
                'prohibited_if:edit_existing_items.*.is_cancelled,true',
                'required_with:edit_existing_items.*.return_date',
                'string',
                'regex:/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',
                'date_format:Y-m-d H:i:s',
                'after:' . now()->tz('Asia/Taipei')->format('Y-m-d H:i:s')
            ],
            'edit_existing_items.*.return_date' => [
                'prohibited_if:edit_existing_items.*.is_cancelled,true',
                'required_with:edit_existing_items.*.start_date',
                'string',
                'regex:/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',
                'date_format:Y-m-d H:i:s',
                'after:items.*.start_date',
            ],
            'edit_existing_items.*.quantity' => [
                'prohibited_if:edit_existing_items.*.is_cancelled,true',
                'integer',
                'min:1',
                'max:3'
            ],
            'edit_existing_items.*.is_cancelled' => [
                Rule::in([true]),
            ],
            /**
             * Add New items ----------------------------------------------------
             */
            'add_new_items' => [
                'array',
                'min:1',
                new UniqueIds,
                new UniqueIdsAcrossArrays($this->all()),

                // On controller because its hard to implement here
                new CheckMaxItemGroupCountPerRequest($this->all()), 
            ],
            'add_new_items.*' => [
                'required',
                'array',
                'size:4',
                // Checks the count of the currently Active Status item in Items Table
                new HasEnoughActiveItems
            ],
            'add_new_items.*.item_group_id' => [
                'required',
                'string',
                'regex:/^[a-zA-Z0-9-]+$/',
                'exists:item_groups,id',
                new ItemGroupDoesNotBelongToBorrowedItems,
            ],
            'add_new_items.*.start_date' => [
                'required',
                'string',
                'regex:/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',
                'date_format:Y-m-d H:i:s',
                'after:' . now()->tz('Asia/Taipei')->format('Y-m-d H:i:s')
            ],
            'add_new_items.*.return_date' => [
                'required',
                'string',
                'regex:/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',
                'date_format:Y-m-d H:i:s',
                'after:items.*.start_date',
            ],
            'add_new_items.*.quantity' => [
                'required',
                'integer',
                'min: 1',
                'max:3'
            ],
        ];
    }
    public function all($keys = null)
    {
        $data = parent::all($keys);
        $data['requestId'] = $this->route('requestId');

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
        $message = "Failed to Submit Request";
        $errorCode = $this->errorCode;
        RequestValidationFailedMsg::errorResponse($validator, $message, $errorCode);
    }

}
