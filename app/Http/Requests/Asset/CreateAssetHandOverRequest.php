<?php

namespace App\Http\Requests\Asset;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class CreateAssetHandOverRequest extends FormRequest
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
    public function rules(Request $request)
    {
        return [
            'asset_ids' => 'required',
            'apartment_id' => 'required',
            'customer' => 'required',
            'email' => 'required|email',
            'phone' => 'required|numeric|digits:10|regex:/(0)[0-9]{9}/',
            'status' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'apartment_id.required' => 'Mã căn hộ không được để trống',
            'customer.required' => 'Khách hàng không được để trống',
            'email.required' => 'Email không được để trống',
            'phone.required' => 'Số điện thoại không được để trống',
            'status.required' => 'Trạng thái không được để trống',
            'asset_ids.required' => 'Mã tài sản không được để trống',
        ];
    }
}
