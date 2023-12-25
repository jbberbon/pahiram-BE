<?php

namespace App\Http\Requests\BorrowTransaction;

use App\Exceptions\RequestExtraPayloadMsg;
use App\Exceptions\RequestValidationFailedMsg;
use App\Models\BorrowTransactionStatus;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CancelBorrowRequest extends FormRequest
{
    private $errorCode = 422;
    private $pending = null;
    public function rules(): array
    {
        $this->pending = BorrowTransactionStatus::where('transac_status_code', 1010)->first();
        return [
            'borrowRequest' => [
                'required',
                Rule::exists('borrow_transactions', 'id')->where(function ($query) {
                    $query->where('transac_status_id', $this->pending->id);
                })
            ],

        ];
    }
    public function all($keys = null)
    {
        $data = parent::all($keys);
        $data['borrowRequest'] = $this->route('borrowRequest');

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
        $message = "Failed to cancel borrowing request";
        $errorCode = $this->errorCode;
        RequestValidationFailedMsg::errorResponse($validator, $message, $errorCode);
    }
}
