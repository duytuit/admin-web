<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class ServiceRequest extends FormRequest
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
        if ($request['bdc_price_type_id'] == 1)
        {
            if(isset($request->ngay_chuyen_doi) && $request->ngay_chuyen_doi != null) {
                return [
                    'name' => 'required|max:190',
                    'bdc_price_type_id' => 'required',
                    'price' => 'required|max:9999999',
                    'bill_date' => 'required|numeric|min:1|max:28',
                    'ngay_chuyen_doi' => 'numeric|min:1|max:30',
                    'payment_deadline' => 'required|numeric|min:1|max:28',
                    'first_time_active' => 'required|date',
                    'description' => 'required',
                    'service_group' => 'required',
                    'type' => 'required'
                ];
            } else {
                return [
                    'name' => 'required|max:190',
                    'bdc_price_type_id' => 'required',
                    'price' => 'required|max:9999999',
                    'bill_date' => 'required|numeric|min:1|max:28',
                    'payment_deadline' => 'required|numeric|min:1|max:28',
                    'first_time_active' => 'required|date',
                    'description' => 'required',
                    'service_group' => 'required',
                    'type' => 'required'
                ];
            }
        }
        if($request['bdc_price_type_id'] == 3) {
            return [
                'name' => 'required|max:190',
                'bdc_price_type_id' => 'required',
                'bill_date' => 'required|numeric|min:1|max:28',
                'payment_deadline' => 'required|numeric|min:1|max:28',
                'first_time_active' => 'required|date',
                'description' => 'required',
                'service_group' => 'required',
                'type' => 'required'
            ];
        } else {
            return [
                'name' => 'required|max:190',
                'bdc_price_type_id' => 'required',
                'bill_date' => 'required|numeric|min:1|max:28',
                'progressive_id' => 'required',
                'payment_deadline' => 'required|numeric|min:1|max:28',
                'first_time_active' => 'required|date',
                'description' => 'required',
                'service_group' => 'required',
                'type' => 'required'
            ];
        }
        if($request['bdc_price_type_id'] == 7) {
            return [
                'partner_id' => 'required',
                'price_free' => 'required',
                'check_confirm' => 'required',
                'persion_register' => 'required'
            ];
        }
    }

    public function messages()
    {
        return [
            'name.required' => 'Tên dịch vụ không được để trống',
            'name.max' => 'Tên dịch vụ không được quá 190 ký tự',
            'bdc_price_type_id.required' => 'Bảng giá không được để trống',
            'bill_date.required' => 'Ngày chốt không được để trống',
            'bill_date.numeric' => 'Ngày chốt phải là số',
            'bill_date.min' => 'Ngày chốt không dưới 1',
            'bill_date.max' => 'Ngày chốt không quá 28',
            'ngay_chuyen_doi.numeric' => 'Ngày chuyển đổi phải là số',
            'ngay_chuyen_doi.min' => 'Ngày chuyển đổi không dưới 1',
            'ngay_chuyen_doi.max' => 'Ngày chuyển đổi không quá 30',
            'price.required' => 'Đơn giá không được để trống',
            'price.max' => 'Đơn giá không quá 9999999',
            'progressive_id.required' => 'Lũy tiến không được để trống',
            'payment_deadline.required' => 'Hạn thanh toán không được để trống',
            'payment_deadline.numeric' => 'Hạn thanh toán phải là số',
            'payment_deadline.min' => 'Hạn thanh toán không dưới 1',
            'payment_deadline.max' => 'Hạn thanh toán không quá 28',
            'first_time_active.required' => 'Áp dụng từ không được để trống',
            'first_time_active.date' => 'Áp dụng từ phải là ngày',
            'description.required' => 'Mô tả không được để trống',
            'service_group.required' => 'Nhóm dịch vụ không được để trống',
            'type.required' => 'Loại dịch vụ không được để trống',
            'partner_id' => 'Đối tác không được để trống'
        ];
    }
}
