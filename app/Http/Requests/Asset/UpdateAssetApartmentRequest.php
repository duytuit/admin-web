<?php

namespace App\Http\Requests\Asset;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssetApartmentRequest extends FormRequest
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
            'building_place_id' => 'required',
            'asset_category_id' => 'required',
            'name' => 'required',
            'code' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'building_place_id.required' => 'Mã tòa nhà không được để trống',
            'asset_category_id.required' => 'Loại tài sản không được để trống',
            'name.required' => 'Tên tài sản không được để trống',
            'code.required' => 'Mã tài sản không được để trống',
        ];
    }
}
