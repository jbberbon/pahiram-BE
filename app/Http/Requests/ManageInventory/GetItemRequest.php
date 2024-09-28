<?php

namespace App\Http\Requests\ManageInventory;

use App\Exceptions\RequestExtraPayloadMsg;
use App\Exceptions\RequestValidationFailedMsg;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class GetItemRequest extends FormRequest
{
    private $errorCode = 422;
    public function rules(): array
    {
        return [
            'item_group_id' => [
                'required',
                'exists:item_groups,id',
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

    /**
     * Merge route parameters with request data for validation.
     */
    public function validationData()
    {
        // Merge route parameters (such as item_group_id) with request data
        return array_merge($this->all(), $this->route()->parameters());
    }
}
