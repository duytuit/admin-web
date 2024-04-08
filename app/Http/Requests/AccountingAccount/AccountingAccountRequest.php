<?php

namespace App\Http\Requests\AccountingAccount;

use Illuminate\Foundation\Http\FormRequest;

class AccountingAccountRequest extends FormRequest
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
            'bank_account' => 'required|numeric|digits_between:1,15',
            'bank_name' => 'required|max:450',
            'holder_name' => 'required|max:450',
            'branch' => 'required|max:450'
        ];
    }
}
