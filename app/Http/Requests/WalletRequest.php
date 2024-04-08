<?php

namespace App\Http\Requests;

use App\Traits\RequestRules;
use Illuminate\Foundation\Http\FormRequest;

class WalletRequest extends FormRequest
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
            'wallet_name' => 'required',
            'currency_code' => 'required',
            'wallet_balance' => 'required|numeric',
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
            'wallet_name' => 'Tên Tài Khoản',
            'currency_code' => 'Đơn vị tiền tệ',
            'wallet_balance' => 'Số Tiền'
        ];
    }
}
