<?php

namespace App\Http\Requests\Asset;

use Illuminate\Foundation\Http\FormRequest;

class CreateAssetRequest extends FormRequest
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
            'buying_date' => 'required|date|before:tomorrow',
            'bdc_assets_type_id' => 'required',
            'place' => 'required|max:191',
            'buyer' => 'required|max:191',
            'price' => 'required|max:15',
            'quantity' => 'required|numeric|min:1',
            'using_peroid' => 'required|numeric|min:1',
            'bdc_period_id' => 'required',
            'maintainance_date' => 'required|date|after:buying_date',
            'warranty_period' => 'required|date|after:buying_date',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Tên tài sản không được để trống',
            'buying_date.required' => 'Ngày mua không được để trống',
            'buyer.required' => 'Người mua không được để trống',
            'place.required' => 'Nơi đặt không được để trống',
            'bdc_assets_type_id.required' => 'Loại tài sản không được để trống',
            'using_peroid.required' => 'Hạn sử dụng không được để trống',
            'using_peroid.numeric' => 'Hạn sử dụng phải là số',
            'using_peroid.min' => 'Hạn sử dụng phải lớn hơn 1',
            'quantity.required' => 'Số lượng không được để trống',
            'quantity.numeric' => 'Số lượng phải là số',
            'quantity.min' => 'Số lượng phải lớn hơn 1',
            'price.required' => 'Số lượng không được để trống',
            'price.numeric' => 'Số lượng phải là số',
            'price.min' => 'Số lượng phải lớn hơn 1000',
            'bdc_period_id.required' => 'Vui lòng chọn loại bảo trì',
            'maintainance_date.after' => 'Ngày bảo trì phải sau ngày mua',
            'maintainance_date.required' => 'Ngày bảo trì không được để trống',
            'maintainance_date.date' => 'Ngày bảo trì phải có định dạng ngày',
            'buying_date.date' => 'Ngày mua phải có định dạng ngày',
            'buying_date.before' => 'Ngày mua không quá ngày hôm nay',
            'warranty_period.after' => 'Hạn bảo hành phải sau ngày mua',
            'warranty_period.required' => 'Hạn bảo hành không được để trống',
            'warranty_period.date' => 'Hạn bảo hành phải có định dạng ngày',
        ];
    }
}
