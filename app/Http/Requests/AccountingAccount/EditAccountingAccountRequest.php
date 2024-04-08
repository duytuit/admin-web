<?php

namespace App\Http\Requests\AccountingAccount;

use Illuminate\Foundation\Http\FormRequest;

class EditAccountingAccountRequest extends FormRequest
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
        $id = request()->id;
        return [
            'bank_account' => 'required|numeric|digits_between:1,15|unique:bdc_payment_info,bank_account,' . $id . ',id',
            'bank_name' => 'required|max:45',
            'holder_name' => 'required|max:45',
            'branch' => 'required|max:45'
        ];
    }
}
