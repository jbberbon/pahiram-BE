<?php

namespace App\Http\Requests\ManageItemCategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use App\Exceptions\RequestExtraPayloadMsg;
use App\Exceptions\RequestValidationFailedMsg;

class ItemCategoryRequest extends FormRequest
{
    private $errorCode = 422;
    public function rules(): array
    {
        if (app()->environment('testing')) {
            return [
                'category_name' => 'nullable|string|max:255',
            ];
        }

        return [
            'category_name' => [
                'sometimes', 
                'string',    
                'max:255', 
                'regex:/^[A-Za-z0-9]+$/'
            ],
        ];
    }

    protected function passedValidation()
    {
        $request = $this->input();
        $rules = $this->rules();
        $errorCode = $this->errorCode;
        if (!app()->environment('testing')) {
            RequestExtraPayloadMsg::errorResponse($request, $rules, $errorCode);
        }
    }

    public function failedValidation(Validator $validator)
    {
        $message = "Failed to retrieve item category data";
        $errorCode = $this->errorCode;
        RequestValidationFailedMsg::errorResponse($validator, $message, $errorCode);
    }
}
