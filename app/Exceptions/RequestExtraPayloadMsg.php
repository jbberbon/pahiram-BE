<?php
namespace App\Exceptions;

// use Exception;
use Illuminate\Http\Exceptions\HttpResponseException;

class RequestExtraPayloadMsg
{
    public static function errorResponse($request, $rules, $errorCode)
    {
        $unexpectedFields = array_diff(array_keys($request), array_keys($rules));
        $method = request()->method();

        if (!empty($unexpectedFields)) {
            throw new HttpResponseException(
                response()->json([
                    'status' => false,
                    'message' => 'The request contains unnecessary fields.',
                    'method' => $method,
                ], $errorCode)
            );
        }
    }
}