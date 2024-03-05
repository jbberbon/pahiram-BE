<?php

namespace App\Http\Requests\ManageInventory;

use App\Exceptions\RequestExtraPayloadMsg;
use App\Exceptions\RequestValidationFailedMsg;
use App\Models\BorrowTransactionStatus;
use App\Rules\IsEmployeeEmail;
use App\Rules\RequestBelongsToUser;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GetItemRequest extends FormRequest
{
    private $errorCode = 422;
    public function rules(): array
    {
        return [
            'itemId' => [
                'required',
                'exists:items,id',
            ]
        ];
    }
    public function all($keys = null)
    {
        $data = parent::all($keys);
        $data['itemId'] = $this->route('itemId');

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
        $message = "Failed to retrieve item data";
        $errorCode = $this->errorCode;
        RequestValidationFailedMsg::errorResponse($validator, $message, $errorCode);
    }
}
