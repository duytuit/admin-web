<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;

class CreateStaffRequest extends FormRequest
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
        $isNew = request()->input('is_new');
        $rules = [
            'name' => 'required|max:191',
            //'phone' => 'required|numeric|digits:10|regex:/(0)[0-9]{9}/|unique:bdc_company_staff,phone',
            //'email' => 'required|max:45|email|unique:bdc_company_staff,email',
            'phone' => 'required|numeric|digits:10|regex:/(0)[0-9]{9}/',
            'email' => 'required|max:45|email',
            'code' => 'required|max:191',
        ];
        if ($isNew == 'true') {
            $rules['password'] ='required|confirmed|min:6';
        }
        return $rules;
    }
}
