<?php

namespace App\Http\Requests\V3\Asset;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;

class Update extends FormRequest
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
            'name' => 'required',
            'asset_category_id' => 'required',
            'quantity' => 'required',
            'area_id' => 'required',
            'department_id' => 'required',
            'follower' => 'required',
            'warranty_period' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'id.required' => 'Id is required!',
            'name.required' => 'Name is required!',
            'asset_category_id.required' => 'Asset Category Id is required!',
            'quantity.required' => 'Quantity is required!',
            'area_id.required' => 'Area Id is required!',
            'department_id.required' => 'Department Id is required!',
            'follower.required' => 'Follower is required!',
            'warranty_period.required' => 'Warranty Period is required!',
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
