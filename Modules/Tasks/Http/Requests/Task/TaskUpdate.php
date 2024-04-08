<?php

namespace Modules\Tasks\Http\Requests\Task;

use App\Http\Requests\CoreRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;

class TaskUpdate extends FormRequest
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
            "department_id" => "required",
            // "work_shift_id" => "required",
            "task_name" => "required",
            "description" => "required",
            "supervisor" => "required",
            "due_date" => "required",
            "start_date" => "required",
            // "type" => "required",
            "task_category_id" => "required",
        ];
    }

    public function messages()
    {
        return [
            'id.required' => 'Work Shift Id is required!',
            'department_id.required' => 'Department Id is required!',
            // 'work_shift_id.required' => 'Work Shift Id is required!',
            'task_name.required' => 'Task name is required!',
            'description.required' => 'Description is required!',
            'supervisor.required' => 'Supervisor is required!',
            'due_date.required' => 'Due date is required!',
            'start_date.required' => 'Start date is required!',
            // 'type.required' => 'Type is required!',
            'task_category_id.required' => 'Task category id is required!',
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
