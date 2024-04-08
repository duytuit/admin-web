<?php

namespace App\Http\Requests\WorkDiaryV2\Shift;

use App\Traits\RequestRules;
use Illuminate\Foundation\Http\FormRequest;

class ShiftRequest extends FormRequest
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
            'name'               => 'required',
            'start_time'               => 'required',
            'end_time'               => 'required'
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
            'name'               => 'Tên ca làm việc',
            'start_time'               => 'Thời gian bắt đầu',
            'end_time'               => 'Thời gian kết thúc'
        ];
    }
}