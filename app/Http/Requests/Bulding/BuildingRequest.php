<?php

namespace App\Http\Requests\Bulding;

use Illuminate\Foundation\Http\FormRequest;

class BuildingRequest extends FormRequest
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
        $id = request()->id;
        return [
            'name' => 'required|max:191|unique:bdc_building,name,' . $id . ',id',
            'description' => 'required',
            //'address' => 'required|max:191|unique:bdc_building,address,' . $id . ',id',
            'phone' => 'required|numeric|min:10|regex:/(0)[0-9]{9}/|unique:bdc_building,phone,' . $id . ',id',
            'email' => 'required|max:45|email|unique:bdc_building,email,' . $id . ',id',
            // 'debit_date' => 'required|numeric|min:1|max:28',
            'bdc_department_id' => 'required|numeric|min:1',
            'vnp_merchant_id' => 'nullable|max:45',
            'vnp_secret' => 'nullable|max:45',
            'vi_viet_merchant_id' => 'nullable|max:45',
            'vi_viet_access_code' => 'nullable|max:45',
            'vi_viet_secret' => 'nullable|max:45',
            'vi_viet_agent_id' => 'nullable|max:45',
        ];
    }
}
