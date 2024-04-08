<?php

namespace App\Http\Requests\ApartmentServicePrice;

use App\Models\Service\Service;
use const App\Repositories\Service\ONE_PRICE;
use const App\Repositories\Service\TYPEVEHICLE;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class ApartmentServicePriceRequest extends FormRequest
{
     private $first_time_active;
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
        if ($request['bdc_service_id'])
        {
            $service = Service::where('id',$request['bdc_service_id'])->first();
            if ($service->bdc_period_id == 6) // chu ky năm
            {
                $this->first_time_active = $service->first_time_active;
                return [
                    'first_time_active' => 'required|date|after_or_equal:'.$service->first_time_active,
                ];
            }
            if ($service->type == TYPEVEHICLE || $service->type == 4)
            {
                if ($service->servicePriceDefault->bdc_price_type_id == 3) // phí dịch vụ đầu kỳ phương tiện
                {
                    return [
                        'bdc_apartment_id' => 'required',
                        'price' => 'required|max:99999999',
                        'first_time_active' => 'required|date',
                    ];
                
                }else if($service->servicePriceDefault->priceType->id == ONE_PRICE)
                {
                    return [
                        'bdc_apartment_id' => 'required',
                        //'bdc_vehicle_id' => 'required|unique:bdc_apartment_service_price,bdc_vehicle_id,'.$this->id,
                        // 'bdc_vehicle_id' => 'required',
                        'price' => 'required|max:99999999',
                        'first_time_active' => 'required|date',
                    ];
                }else {
                    return [
                        'bdc_apartment_id' => 'required',
                        //'bdc_vehicle_id' => 'required|unique:bdc_apartment_service_price,bdc_vehicle_id,'.$this->id,
                        // 'bdc_vehicle_id' => 'required',
                        'first_time_active' => 'required|date',
                    ];
                }

            } elseif($service->type == 0) {
                if ($service->servicePriceDefault->priceType->id == ONE_PRICE)
                {
                    return [
                        'bdc_apartment_id' => 'required',
                        'bdc_service_id' => 'required',
                        'price' => 'required|max:99999999',
                        'first_time_active' => 'required|date',
                    ];
                } else {
                    return [
                        'bdc_apartment_id' => 'required',
                        'bdc_service_id' => 'required',
                        'first_time_active' => 'required|date',
                    ];
                }
            } else{
                if ($service->servicePriceDefault->priceType->id == ONE_PRICE)
                {
                    return [
                        'bdc_apartment_id' => 'required',
                        'bdc_service_id' => 'required',
                        'floor_price' => 'required|max:99999999',
                        'first_time_active' => 'required|date',
                    ];
                } else {
                    return [
                        'bdc_apartment_id' => 'required',
                        'bdc_service_id' => 'required',
                        'first_time_active' => 'required|date',
                    ];
                }
            }
        }
        return [
            'bdc_apartment_id' => 'required',
            'bdc_service_id' => 'required',
            'first_time_active' => 'required|date',
        ];
    }

    public function messages()
    {
        return [
            'bdc_apartment_id.required' => 'Căn hộ không được để trống',
            'bdc_service_id.required' => 'Dịch vụ không được để trống',
            'bdc_vehicle_id.required' => 'Tên phương tiện không được để trống',
            'bdc_vehicle_id.unique' => 'Tên phương tiện đã tồn tại',
            'bdc_price_type_id.required' => 'Bảng giá không được để trống',
            'bill_date.required' => 'Ngày chốt không được để trống',
            'bill_date.numeric' => 'Ngày chốt phải là số',
            'bill_date.min' => 'Ngày chốt không dưới 1',
            'bill_date.max' => 'Ngày chốt không quá 28',
            'price.required' => 'Đơn giá không được để trống',
            'price.max' => 'Đơn giá không quá 9999999',
            'floor_price.required' => 'Đơn giá không được để trống',
            'floor_price.max' => 'Đơn giá không quá 9999999',
            'progressive_id.required' => 'Lũy tiến không được để trống',
            'payment_deadline.required' => 'Hạn thanh toán không được để trống',
            'payment_deadline.numeric' => 'Hạn thanh toán phải là số',
            'payment_deadline.min' => 'Hạn thanh toán không dưới 1',
            'payment_deadline.max' => 'Hạn thanh toán không quá 28',
            'first_time_active.required' => 'Áp dụng từ không được để trống',
            'first_time_active.date' => 'Áp dụng từ phải là ngày',
            'first_time_active.after_or_equal' => 'Ngày bắt đầu phải lớn hơn hoặc bằng ngày áp dụng dịch vụ: '.$this->first_time_active,
            'description.required' => 'Mô tả không được để trống'
        ];
    }
}
