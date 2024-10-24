<?php

namespace App\Http\Requests\ManageBorrowTransaction;


use App\Exceptions\RequestExtraPayloadMsg;
use App\Exceptions\RequestValidationFailedMsg;
use App\Rules\AcceptOnlyAllowedObjFields;
use App\Rules\ManageTransactionRules\IsBorrowedItemPartOfTransaction;
use App\Rules\ManageTransactionRules\IsItemGroupPartOfTransaction;
use App\Rules\ManageTransactionRules\IsItemInPossessionOrUnreturned;
use App\Rules\ManageTransactionRules\IsThereItemLeftToReturn;
use App\Rules\ManageTransactionRules\IsTransactionOnGoingOrUnreturned;
use App\Rules\ManageTransactionRules\ValidateReturnItemStatus;
use App\Rules\UniqueBorrowedItemIds;
use App\Rules\UniqueItemGroupIds;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FacilitateReturnRequest extends FormRequest
{
    private $errorCode = 422;
    private $returnedItemStatusArray = array_values(BORROWED_ITEM_STATUS::RETURNED_STATUSES);
    private $unreturnedItemStatusArray = array_values(BORROWED_ITEM_STATUS::UNRETURNED_STATUSES);

    public function rules(): array
    {
        return [
            'transactionId' => [
                'required',
                'exists:borrow_transactions,id',
                new IsTransactionOnGoingOrUnreturned,
            ],
            // 'return_all_items' => [
            //     'required_without:items', // Required if 'items' is not present
            //     'required_without_all:items', // Required if none of the 'items' are present
            //     new AcceptOnlyAllowedObjFields(['is_returned', 'transac_remarks']),
            //     new IsThereItemLeftToReturn($this->all()),
            // ],
            // 'return_all_items.is_returned' => [
            //     'required_without:items', // Required if 'items' is not present
            //     'required_without_all:items', // Required if none of the 'items' are present
            //     'bool',
            // ],
            // 'return_all_items.transac_remarks' => [
            //     'sometimes',
            //     'string',
            //     'regex:/^[a-zA-Z0-9-]+$/',
            //     'max:50'
            // ],

            'items' => [
                'required',
                'array',
                'min:1',
                'max:10',
                new UniqueBorrowedItemIds,
                new IsThereItemLeftToReturn($this->all()),
            ],
            'items.*' => [
                'required',
                'array',
                'min:2',
                'max:5',
                new AcceptOnlyAllowedObjFields([
                    'borrowed_item_id',
                    'is_returned',
                    'item_status',
                    'item_penalty',
                    'item_remarks'
                ]),
            ],
            'items.*.borrowed_item_id' => [
                'required',
                'string',
                'regex:/^[a-zA-Z0-9-]+$/',
                'exists:borrowed_items,id',
                new IsBorrowedItemPartOfTransaction($this->all()),
                // Ofc you can only return currently possessed item or unreturned item
                new IsItemInPossessionOrUnreturned($this->all()),
            ],
            'items.*.item_status' => [
                'required',
                'string',
                Rule::in([
                    ...$this->returnedItemStatusArray,
                    ...$this->unreturnedItemStatusArray
                ]),
            ],
            'items.*.item_penalty' => [
                'sometimes',
                'numeric',
                'between:1,1000000',
            ],
            'items.*.remarks_by_receiver' => [
                'sometimes',
                'string',
                'max:400'
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
        $message = "Failed to return items";
        $errorCode = $this->errorCode;
        RequestValidationFailedMsg::errorResponse($validator, $message, $errorCode);
    }
}
