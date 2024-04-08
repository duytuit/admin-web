<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;

abstract class AbstractRequest extends FormRequest
{
    /**
     * Get the proper failed validation response for the request.
     *
     * @param  array  $errors
     * @return JsonResponse
     */
    public function response(array $errors)
    {
        if ($this->expectsJson()) {
            return response()->json([
                'data' => $errors,
                'code' => 422,
                'message' => 'Validation error'
            ], 422);
        }
    }
}
