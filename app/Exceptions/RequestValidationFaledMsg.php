<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Utils\ValidatorReturnDataCleanup;

class RequestValidationFaledMsg extends Exception
{
    /**
     * Throw an exception with request validation errors.
     *
     * @param object $validator
     * @param string $message
     * @param string $method
     * @param int $errorCode
     *
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    public static function errorResponse($validator, $message, $errorCode)
    {
        $errors = $validator->errors()->get('*');
        $method = request()->method();
        throw new HttpResponseException(
            response([
                'status' => false,
                'message' => $message,
                'errors' => ValidatorReturnDataCleanup::cleanup($errors),
                'method' => $method,
            ], $errorCode)
        );
    }
}
