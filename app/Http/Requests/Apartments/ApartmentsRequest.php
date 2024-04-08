<?php

namespace App\Http\Requests\Apartments;

use App\Traits\RequestRules;
use Illuminate\Foundation\Http\FormRequest;

class ApartmentsRequest extends FormRequest
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
        $id = request()->id;
        $rules = [
            'name'       => 'required|max:45',
            'floor' => 'required',
            'status' => 'required',
            //'code_customer' => 'unique:bdc_apartments,code_customer,' . $id . ',id',
        ];
        return $this->rulesByMethod($rules);
    }

    public function attributes()
    {
        return [
            'name' => 'Tên căn hộ',
            'floor' => 'Tầng',
            'status' => 'Trạng thái',
            'code_customer' => 'Mã khách hàng',
        ];

    }
    public function messages()
    {
        return [
            'name.required' => 'Tên căn hộ không được để trống',
            'name.max' => 'Tên căn hộ không được quá 45 kí tự',
            'floor.required' => 'Tâng không được để trống',
            'code_customer.unique' => 'Mã khách hàng không được trùng',
        ];
    }
}
