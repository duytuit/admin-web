<?php

namespace App\Http\Requests\WorkDiary;

use App\Traits\RequestRules;
use Illuminate\Foundation\Http\FormRequest;

class WorkDiaryRequest extends FormRequest
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
            'title'               => 'required|max:191',
            'description'         => 'required',
            'assign_to'           => 'required',
            'bdc_department_id'   => 'required',
            'start_at'            => 'required',
            'end_at'              => 'required|after_or_equal:start_at',
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
            'title'               => 'tiêu đề',
            'description'         => 'mô tả',
            'assign_to'           => 'người xử lý',
            'bdc_department_id'   => 'bộ phận tiếp nhận',
            'start_at'            => 'ngày bắt đầu',
            'end_at'              => 'ngày kết thúc',
        ];
    }
}