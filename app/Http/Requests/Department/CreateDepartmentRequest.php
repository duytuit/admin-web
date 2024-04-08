<?php

namespace App\Http\Requests\Department;

use Illuminate\Foundation\Http\FormRequest;

class CreateDepartmentRequest extends FormRequest
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
            'name' => 'required|max:191',
            'code' => 'required|max:45',
            'phone' => 'nullable|numeric|digits:10|regex:/(0)[0-9]{9}/',
            'email' => 'nullable|max:45|email'
        ];
    }
}
