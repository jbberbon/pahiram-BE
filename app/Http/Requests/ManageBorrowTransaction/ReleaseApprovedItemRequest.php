<?php

namespace App\Http\Requests\ManageBorrowTransaction;

use App\Exceptions\RequestExtraPayloadMsg;
use App\Exceptions\RequestValidationFailedMsg;
use App\Rules\ManageTransactionRules\IsBorrowApproverFromCorrectOffice;
use App\Rules\ManageTransactionRules\IsBorrowedItemPartOfTransaction;
use App\Rules\ManageTransactionRules\IsEarlyToReleaseItem;
use App\Rules\ManageTransactionRules\IsItemApproved;
use App\Rules\ManageTransactionRules\IsThereItemLeftToRelease;
use App\Rules\ManageTransactionRules\IsTransactionApprovedOrOngoingStatus;
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
                new IsTransactionApprovedOrOngoingStatus,
                new IsBorrowApproverFromCorrectOffice

            ],
            'release_all_items' => [
                'required_without:items', // Required if 'items' is not present
                function ($attribute, $value, $fail) {
                    if (isset($this->items)) {
                        $fail('Both release_all_items and items cannot be present at the same time.');
                    }
                },
                'boolean',
                new IsThereItemLeftToRelease($this->all()),

                // IsThereItemWithLapsedReturnDate (disallow if there is even 1) // Disallow release if current time > return date
            ],
            'items' => [
                'required_without:release_all_items',
                function ($attribute, $value, $fail) {
                    if (isset($this->release_all_items)) {
                        $fail('Both items and release_all_items cannot be present at the same time.');
                    }
                },
                'array',
                'min:1',
                'max:10',

                // Check for uniqueness of IDs
                function ($attribute, $value, $fail) {
                    // Flatten the nested array and check if the 'borrowed_item_id' values are unique
                    $ids = collect($value)->pluck('borrowed_item_id');
                    if ($ids->count() !== $ids->unique()->count()) {
                        $fail('The borrowed_item_id values must be unique.');
                    }
                }
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
                new IsEarlyToReleaseItem // Disallow release if current time > return date
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
