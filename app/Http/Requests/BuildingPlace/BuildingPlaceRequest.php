<?php

namespace App\Http\Requests\BuildingPlace;

use App\Traits\RequestRules;
use Illuminate\Foundation\Http\FormRequest;

class BuildingPlaceRequest extends FormRequest
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
    public function rules()
    {
        $rules = [
            'name'       => 'required|max:45',
        ];
        return $this->rulesByMethod($rules);
    }

    public function attributes()
    {
        return [
            'name' => 'Tên tòa nhà',
        ];

    }
    public function messages()
    {
        return [
            'name.required' => 'Tên tòa nhà không được để trống',
            'name.max' => 'Tên tòa nhà không được quá 45 kí tự',
        ];
    }
}
