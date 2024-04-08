<?php

namespace App\Http\Requests\BuildingHandbook;

use App\Traits\RequestRules;
use Illuminate\Foundation\Http\FormRequest;

class BuildingHandbookRequest extends FormRequest
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
            'title'                    => 'required|max:191',
            'content'                  => 'required',
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
          'title'                        => 'tiêu đề',
          'content'                      => 'nội dung',
        ];
    }
}