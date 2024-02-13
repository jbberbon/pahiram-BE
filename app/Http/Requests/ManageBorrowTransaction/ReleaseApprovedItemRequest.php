<?php

namespace App\Http\Requests\ManageBorrowTransaction;

use App\Exceptions\RequestExtraPayloadMsg;
use App\Exceptions\RequestValidationFailedMsg;
use App\Rules\AcceptOnlyAllowedObjFields;
use App\Rules\ManageTransactionRules\IsBorrowedItemPartOfTransaction;
use App\Rules\ManageTransactionRules\IsItemApproved;
use App\Rules\ManageTransactionRules\IsThereItemLeftToRelease;
use App\Rules\ManageTransactionRules\IsTransactionApprovedStatus;
use App\Rules\UniqueBorrowedItemIds;
use App\Rules\UniqueItemGroupIds;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class ReleaseApprovedItemRequest extends FormRequest
{
    private $errorCode = 422;
    public function rules(): array
    {
        return [
            'transactionId' => [
                'required',
                'exists:borrow_transactions,id',
                new IsTransactionApprovedStatus,
            ],
            'release_all_items' => [
                'required_without:items', // Required if 'items' is not present
                'required_without_all:items', // Required if none of the 'items' are present
                'boolean',
                new IsThereItemLeftToRelease($this->all()),

                // IsThereItemWithLapsedReturnDate (disallow if there is even 1) // Disallow release if current time > return date
            ],
            'items' => [
                'required_without:release_all_items',
                'required_without_all:release_all_items',
                'prohibited_if:release_all_items,true',
                function ($attribute, $value, $fail) {
                    $request = $this->all();

                    if (isset($request['release_all_items'])) {
                        $fail('Invalid request');
                    }
                },
                'array',
                'min:1',
                'max:10',
                new UniqueBorrowedItemIds,
                new IsThereItemLeftToRelease($this->all()),
            ],
            'items.*' => [
                'required',
                'array',
                'size:2'
            ],
            'items.*.borrowed_item_id' => [
                'required',
                'string',
                'regex:/^[a-zA-Z0-9-]+$/',
                'exists:borrowed_items,id',
                new IsBorrowedItemPartOfTransaction($this->all()),
                new IsItemApproved($this->all()),

                // IsItemReturnDateLapsed // Disallow release if current time > return date
            ],
            'items.*.is_released' => [
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
        $message = "Failed to release items";
        $errorCode = $this->errorCode;
        RequestValidationFailedMsg::errorResponse($validator, $message, $errorCode);
    }
}
