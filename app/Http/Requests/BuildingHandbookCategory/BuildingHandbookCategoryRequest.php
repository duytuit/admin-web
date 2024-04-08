<?php

namespace App\Http\Requests\BuildingHandbookCategory;

use App\Traits\RequestRules;
use Illuminate\Foundation\Http\FormRequest;

class BuildingHandbookCategoryRequest extends FormRequest
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
            'name'                 => 'required|max:191',
            'bdc_handbook_type_id' => 'required',
            'phone' => 'nullable|numeric|regex:/^[0-9]{8,11}+$/',
        ];

        return $this->rulesByMethod($rules);
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
          'name'                   => 'tiêu đề',
          'bdc_handbook_type_id'   => 'phân loại',
        ];
    }
}