<?php

namespace App\Http\Requests\CustomerRatedServices;

use App\Traits\RequestRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class CreateRequest extends FormRequest
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
    public function rules(Request $request)
    {
        if ($request->user_id)
        {
            return [
                'danh_gia' =>  'required|numeric',
            ];
        }
        else if($request->vang_lai){
            return [
                'customer' => 'required',
                'phone' => 'required|nullable|numeric|digits:10|regex:/(0)[0-9]{9}/',
                'danh_gia' =>  'required|numeric',
            ];
        }
        else{
            return [
                'customer' => 'required',
                'apartment_name'=> 'required',
                'phone' => 'required|nullable|numeric|digits:10|regex:/(0)[0-9]{9}/',
                'danh_gia' =>  'required|numeric',
            ];
        }
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'customer' => 'tên khách hàng',
            'phone' => 'số điện thoại khách hàng',
            'apartment_name' => 'căn hộ khách hàng',
            'danh_gia' =>  'loại đánh giá',
            'employee_id' =>  'mã nhân viên',
        ];
    }
}
