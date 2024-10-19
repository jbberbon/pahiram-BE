<?php

namespace App\Http\Requests\ManageBorrowTransaction;

use App\Exceptions\RequestExtraPayloadMsg;
use App\Exceptions\RequestValidationFailedMsg;
use App\Rules\ManageTransactionRules\IsAuthorizedToApproveAllItems;
use App\Rules\ManageTransactionRules\IsBorrowApproverFromCorrectOffice;
use App\Rules\ManageTransactionRules\IsEmployeeAuthorizedToApproveBorrowedItem;
use App\Rules\ManageTransactionRules\IsItemGroupPartOfTransaction;
use App\Rules\ManageTransactionRules\IsItemPendingApproval;
use App\Rules\ManageTransactionRules\IsThereItemLeftToApprove;
use App\Rules\ManageTransactionRules\IsTransactionPendingBorrowApprovalStatus;
use App\Rules\UniqueItemGroupIds;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class ApproveTransactionRequest extends FormRequest
{
    private $errorCode = 422;
    public function rules(): array
    {
        return [
            'transactionId' => [
                'required',
                'regex:/^[a-zA-Z0-9-]+$/',
                'exists:borrow_transactions,id',
                new IsTransactionPendingBorrowApprovalStatus,
                new IsBorrowApproverFromCorrectOffice
            ],
            'approve_all_items' => [
                'required_without:items', // Required if 'items' is not present
                function ($attribute, $value, $fail) {
                    if (isset($this->items)) {
                        $fail('Both approve_all_items and items cannot be present at the same time.');
                    }
                },
                'boolean',
                new IsAuthorizedToApproveAllItems($this->all()),
                new IsThereItemLeftToApprove($this->all())

                // IsThereItemWithLapsedStartDate (disallow if there is even 1)  // Disallow approval if current time > start date
            ],
            'items' => [
                'required_without:approve_all_items', // Required if 'approve_all_items' is not present
                function ($attribute, $value, $fail) {
                    if (isset($this->approve_all_items)) {
                        $fail('Both items and approve_all_items cannot be present at the same time.');
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
                new IsEmployeeAuthorizedToApproveBorrowedItem($this->all()),
                new IsItemGroupPartOfTransaction($this->all()),
                new IsItemPendingApproval

                // IsItemApprovalStartDateLapsed // Disallow approval if current time > start date
            ],
            'items.*.is_approved' => [
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
