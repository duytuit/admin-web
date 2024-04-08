<?php

namespace App\Http\Requests\Building;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBuildingCompanyRequest extends FormRequest
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
            'name' => 'required|max:191',
            'description' => 'required',
            'address' => 'required|max:191',
            'urban_id' => 'required',
            'phone' => 'numeric|min:10|regex:/(0)[0-9]{9}/',
            'email' => 'max:45|email',
            // 'manager_id' => 'required',
            'vnp_merchant_id' => 'nullable|max:45',
            'vnp_secret' => 'nullable|max:45',
            'vi_viet_secret' => 'nullable|max:45',
            'vi_viet_agent_id' => 'nullable|max:45',
        ];
    }
    public function attributes()
    {
        return [
            'urban_id' => 'Khu đô thị',
        ];
    }
}
