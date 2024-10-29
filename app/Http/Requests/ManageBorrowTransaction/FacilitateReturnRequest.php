<?php

namespace App\Http\Requests\ManageBorrowTransaction;


use App\Exceptions\RequestExtraPayloadMsg;
use App\Exceptions\RequestValidationFailedMsg;
use App\Rules\AcceptOnlyAllowedObjFields;
use App\Rules\ManageTransactionRules\IsBorrowedItemPartOfTransaction;
use App\Rules\ManageTransactionRules\IsItemInPossessionOrUnreturned;
use App\Rules\ManageTransactionRules\IsThereItemLeftToReturn;
use App\Rules\ManageTransactionRules\IsTransactionOnGoingOrUnreturned;
use App\Rules\ManageTransactionRules\ReturnItems\IsPenaltyRequiredForReturnedItems;
use App\Rules\UniqueBorrowedItemIds;
use App\Utils\Constants\Statuses\BORROWED_ITEM_STATUS;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FacilitateReturnRequest extends FormRequest
{
    private $errorCode = 422;

    public function rules(): array
    {
        $returnedItemStatusArray = array_values(BORROWED_ITEM_STATUS::RETURNED_STATUSES);
        $unreturnedItemStatusArray = array_values(BORROWED_ITEM_STATUS::UNRETURNED_STATUSES);
        return [
            'transactionId' => [
                'required',
                'string',
                'exists:borrow_transactions,id',
                new IsTransactionOnGoingOrUnreturned,
            ],

            'items' => [
                'required',
                'array',
                'min:1',
                'max:10',
                new UniqueBorrowedItemIds,
                new IsThereItemLeftToReturn($this->all()),
                
                // Make penalty required when the status is part of the unreturnedItemStatusArr
                new IsPenaltyRequiredForReturnedItems($this->all()),
            ],
            'items.*' => [
                'required',
                'array',
                'min:2',
                'max:5',
                new AcceptOnlyAllowedObjFields([
                    'borrowed_item_id',
                    'item_status',
                    'penalty',
                    'remarks_by_receiver'
                ]),
            ],
            'items.*.borrowed_item_id' => [
                'required',
                'string',
                'regex:/^[a-zA-Z0-9-]+$/',
                'exists:borrowed_items,id',
                new IsBorrowedItemPartOfTransaction($this->all()),
                // Ofc you can only return currently possessed item or unreturned item
                new IsItemInPossessionOrUnreturned,
            ],
            'items.*.item_status' => [
                'required',
                'string',
                Rule::in([
                    ...$returnedItemStatusArray,
                    ...$unreturnedItemStatusArray
                ]),
            ],
            'items.*.penalty' => [
                'numeric',
                'between:1,1000000',
            ],
            'items.*.remarks_by_receiver' => [
                'required',
                'string',
                'min:10',
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
