<?php

namespace App\Http\Requests\Feedback;

use Illuminate\Foundation\Http\FormRequest;

class CreateWarrantyClaimRequest extends FormRequest
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
            'bdc_apartment_id' => 'required',
            'full_name' => 'required',
            'email' => 'required|email',
            'phone' => 'required|numeric|digits:10|regex:/(0)[0-9]{9}/',
            'asset_id' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
            'title' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'bdc_apartment_id.required' => 'Mã căn hộ không được để trống',
            'full_name.required' => 'Khách hàng không được để trống',
            'email.required' => 'Email không được để trống',
            'phone.required' => 'Số điện thoại không được để trống',
            'asset_id.required' => 'Mã tài sản không được để trống',
            'start_time.required' => 'Thời gian bắt đầu không được để trống',
            'end_time.required' => 'Thời gian kết thúc không được để trống',
            'assetitlet_id.required' => 'Tiêu đề không được để trống',
        ];
    }
}
