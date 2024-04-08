<?php

namespace App\Http\Requests\V3\Asset;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;

class Add extends FormRequest
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
            'building_id' => 'required',
            'name' => 'required',
            'asset_category_id' => 'required',
            'quantity' => 'required',
            // 'bdc_period_id' => 'required',
            // 'maintainance_date' => 'required',
            'area_id' => 'required',
            'department_id' => 'required',
            'follower' => 'required',
            'warranty_period' => 'required',
            // 'asset_note' => 'required',
            // 'images' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'building_id.required' => 'Building Id is required!',
            'name.required' => 'Name is required!',
            'asset_category_id.required' => 'Asset Category Id is required!',
            'quantity.required' => 'Quantity is required!',
            // 'bdc_period_id.required' => 'Period Id is required!',
            // 'maintainance_date.required' => 'Maintainance Date is required!',
            'area_id.required' => 'Area Id is required!',
            'department_id.required' => 'Department Id is required!',
            'follower.required' => 'Follower is required!',
            'warranty_period.required' => 'Warranty Period is required!',
            // 'asset_note.required' => 'Asset note is required!',
            // 'images.required' => 'Images is required!',
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
