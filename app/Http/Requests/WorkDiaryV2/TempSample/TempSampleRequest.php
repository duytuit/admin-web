<?php

namespace App\Http\Requests\WorkDiaryV2\TempSample;

use App\Traits\RequestRules;
use Illuminate\Foundation\Http\FormRequest;

class TempSampleRequest extends FormRequest
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
            'title'               => 'required',
            'department_ids'               => 'required',
            'sub_task_template_infos'               => 'required'
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
            'title'=> 'Mẫu tiêu đề',
            'department_ids' => 'Bộ phận',
            'sub_task_template_infos'=> 'Công việc nhỏ'
        ];
    }
}