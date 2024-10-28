<?php

namespace App\Http\Requests\ManagePenalty;

use App\Exceptions\RequestExtraPayloadMsg;
use App\Exceptions\RequestValidationFailedMsg;
use App\Rules\AcceptOnlyAllowedObjFields;
use App\Rules\ManagePenalizedLendingTransactionRules\BorrowedItemIsPenalized;
use App\Rules\ManagePenalizedLendingTransactionRules\IsBorrowedItemPenaltyAmountAlreadyAdjusted;
use App\Rules\ManagePenalizedLendingTransactionRules\IsPenalizedTransactionPendingLendingSupervisorFinalization;
use App\Rules\ManageTransactionRules\IsBorrowedItemPartOfTransaction;
use App\Rules\UniqueBorrowedItemIds;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class FinalizeLendingOfficePenaltyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'transactionId' => [
                'required',
                'string',
                'exists:penalized_transactions,borrowing_transac_id',
                new IsPenalizedTransactionPendingLendingSupervisorFinalization
            ],

            'items' => [
                'required',
                'array',
                'min:1',
                'max:20',
                new UniqueBorrowedItemIds,
            ],
            'items.*' => [
                'required',
                'array',
                'min:2',
                'max:5',
                new AcceptOnlyAllowedObjFields(
                    allowedFields: [
                        'borrowed_item_id',
                        'penalty',
                        'remarks_by_penalty_finalizer'
                    ]
                ),
            ],
            'items.*.borrowed_item_id' => [
                'required',
                'string',
                'regex:/^[a-zA-Z0-9-]+$/',
                'exists:borrowed_items,id',
                new IsBorrowedItemPartOfTransaction($this->all()),
                new BorrowedItemIsPenalized,
                new IsBorrowedItemPenaltyAmountAlreadyAdjusted
            ],
            'items.*.penalty' => [
                'numeric',
                'between:1,1000000',
            ],
            'items.*.remarks_by_penalty_finalizer' => [
                'required',
                'string',
                'min:10',
                'max:400'
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
        $message = "Failed to finalize penalty.";
        $errorCode = $this->errorCode;
        RequestValidationFailedMsg::errorResponse($validator, $message, $errorCode);
    }
}