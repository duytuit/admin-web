<?php

namespace App\Http\Requests\CustomerRatedServices;

use App\Traits\RequestRules;
use Illuminate\Foundation\Http\FormRequest;

class CustomerRatedServiceRequest extends FormRequest
{
  use RequestRules;
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
        $rules = [
            'customer_name' => 'required',
            'email'  => 'nullable|max:45|email',
            'phone' => 'nullable|numeric|digits:10|regex:/(0)[0-9]{9}/',
            'rated' =>  'required',
            'employee_id' =>  'required',
            'department_id' => 'required',
        ];

        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'customer_name' => 'tên khách hàng',
            'email'  => 'email khách hàng',
            'phone' => 'số điện thoại khách hàng',
            'rated' =>  'loại đánh giá',
            'employee_id' =>  'mã nhân viên',
            'department_id' => 'mã bộ phận',
        ];
    }
}
