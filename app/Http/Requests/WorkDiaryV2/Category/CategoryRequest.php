<?php

namespace App\Http\Requests\WorkDiaryV2\Category;

use App\Traits\RequestRules;
use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
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
            'category_name'               => 'required',
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
            'category_name'               => 'Tên ca danh mục công việc',
        ];
    }
}