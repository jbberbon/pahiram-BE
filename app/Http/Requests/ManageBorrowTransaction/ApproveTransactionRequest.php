<?php

namespace App\Http\Requests\ManageBorrowTransaction;

use App\Exceptions\RequestExtraPayloadMsg;
use App\Exceptions\RequestValidationFailedMsg;
use App\Rules\ManageTransactionRules\IsEmployeeAuthorizedToApproveAllItems;
use App\Rules\ManageTransactionRules\IsEmployeeAuthorizedToApproveBorrowedItem;
use App\Rules\ManageTransactionRules\IsItemGroupPartOfTransaction;
use App\Rules\ManageTransactionRules\IsItemPendingApproval;
use App\Rules\ManageTransactionRules\IsTransactionPendingBorrowApprovalStatus;
use App\Rules\UniqueItemGroupIds;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApproveTransactionRequest extends FormRequest
{
    private $errorCode = 422;
    public function rules(): array
    {
        return [
            'transactionId' => [
                'required',
                'exists:borrow_transactions,id',
                new IsTransactionPendingBorrowApprovalStatus
            ],
            'approve_all_items' => [
                'required_without:items', // Required if 'items' is not present
                'required_without_all:items', // Required if none of the 'items' are present
                'boolean',
                new IsEmployeeAuthorizedToApproveAllItems($this->all())
            ],
            'items' => [
                'required_without:approve_all_items',
                'required_without_all:approve_all_items',
                'prohibited_if:approve_all_items,true',
                'array',
                'min:1',
                'max:10',
                new UniqueItemGroupIds
            ],
            'items.*' => [
                'required',
                'array',
                'size:2'
            ],
            'items.*.item_group_id' => [
                'required',
                'string',
                'regex:/^[a-zA-Z0-9-]+$/',
                'exists:item_groups,id',
                new IsEmployeeAuthorizedToApproveBorrowedItem($this->all()),
                new IsItemGroupPartOfTransaction($this->all()),
                new IsItemPendingApproval($this->all())
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
        $message = "Failed to update transaction approval";
        $errorCode = $this->errorCode;
        RequestValidationFailedMsg::errorResponse($validator, $message, $errorCode);
    }
}
