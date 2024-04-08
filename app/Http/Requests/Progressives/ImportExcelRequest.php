<?php

namespace App\Http\Requests\Progressives;

use App\Traits\RequestRules;
use Illuminate\Foundation\Http\FormRequest;

class ImportExcelRequest extends FormRequest
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
            'file_import' => 'required',
            'cycle_name' => 'required|numeric|digits:6',
            'deadline' => 'required',
            'from_date' => 'required',
            'to_date' => 'required'
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
            'file_import' => 'File excel',
            'deadline' => 'Hạn thanh toán',
            'cycle_name' =>  'kỳ bảng kê'
        ];
    }
}