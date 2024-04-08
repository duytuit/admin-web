<?php

namespace Modules\Tasks\Http\Requests\TaskUser;

use App\Http\Requests\CoreRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;

class TaskCommentAdd extends FormRequest
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
            "task_id" => "required",
            "user_id" => "required",
            "comment" => "required",
        ];
    }

    public function messages()
    {
        return [
            'task_id.required' => 'TaskId is required!',
            'user_id.required' => 'UserId is required!',
            'comment.required' => 'Comment is required!',
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
