<?php

namespace App\Http\Requests\BuildingHandbookType;

use App\Traits\RequestRules;
use Illuminate\Foundation\Http\FormRequest;

class BuildingHandbookTypeRequest extends FormRequest
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
            'name'   => 'required|max:191',
            'type_company' =>'required|max:191',
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
          'name'           => 'tên',
          'type_company'           => 'type company',
        ];
    }
}