<?php

namespace App\Http\Requests\Building;

use Illuminate\Foundation\Http\FormRequest;

class CreateBuildingRequest extends FormRequest
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
            'name' => 'required|max:191|unique:bdc_building,name',
            'building_code' => 'required|max:191|unique:bdc_building,building_code',
            'description' => 'required',
            'address' => 'required|max:191|unique:bdc_building,address',
            'phone' => 'required|numeric|min:10|regex:/(0)[0-9]{9}/|unique:bdc_building,phone',
            'email' => 'required|max:45|email|unique:bdc_building,email',
            // 'manager_id' => 'required',
            'company_id' => 'required',
            'urban_id' => 'required',
            'vnp_merchant_id' => 'nullable|max:45',
            'vnp_secret' => 'nullable|max:45',
            'vi_viet_secret' => 'nullable|max:45',
            'vi_viet_agent_id' => 'nullable|max:45',
        ];
    }

    public function attributes()
    {
        return [
            'user_id' => 'Trưởng ban quản lý',
        ];
    }
}
