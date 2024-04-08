<?php

namespace App\Http\Requests\Setting;

use Illuminate\Foundation\Http\FormRequest;

class ConfigSendMailRequest extends FormRequest
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
        return [
            'status' => 'required',
            'mail_template_id' => 'required'
        ];
    }
    public function messages()
    {
        return [
            'status.required' => 'Bạn cần phải trạng thái',
            'mail_template_id.required' => 'Bạn cần phải chọn mẫu email'
        ];
    }
}
