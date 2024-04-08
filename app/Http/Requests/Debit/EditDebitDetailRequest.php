<?php

namespace App\Http\Requests\Debit;

use Illuminate\Foundation\Http\FormRequest;

class EditDebitDetailRequest extends FormRequest
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
        return[
            'price' =>'required|numeric|min:0|digits_between:1,10',
            'from_date' => 'required|date|before:to_date',
        ];
    }

    public function attributes()
    {
        return [
            'price' => 'đơn giá',
            'from_date' => 'ngày bắt đầu dịch vụ',
            'to_date' => 'ngày chốt công nợ',
        ];
    }
}
