<?php

namespace Modules\Tasks\Http\Requests\WorkShift;

use App\Http\Requests\CoreRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;

class WorkShiftUpdate extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "id" => "required",
            "name" => "required",
            "start_time" => "required",
            "end_time" => "required",
        ];
    }

    public function messages()
    {
        return [
            'id.required' => 'Work Shift Id is required!',
            'name.required' => 'Title is required!',
            'start_time.required' => 'Start time is required!',
            'end_time.required' => 'End time is required!',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();
        $message = "";
        foreach($errors as $_error) {
            $message = $_error[0];
        }
        throw new HttpResponseException(response()->json(
            [
                'success' => false,
                'message' => $message,
                'error' => $errors,
                'status' => 422,
            ],
            JsonResponse::HTTP_OK
        ));
    }
}
