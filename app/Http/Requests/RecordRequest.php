<?php

namespace App\Http\Requests;

use App\Traits\RequestRules;
use Illuminate\Foundation\Http\FormRequest;

class RecordRequest extends FormRequest
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
     * @return void
     */
    public function rules()
    {
        $rules = [
            'wallet_id' => 'required',
            'category' => 'required',
            'record_type' => 'required|numeric',
            'amount' => 'required|numeric',
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
            'wallet_id' => 'Id tài khoản cha',
            'category' => 'Hạng mục chi',
            'record_type' => 'Loại bản ghi',
            'amount' => 'Số Tiền'
        ];
    }
}
