<?php

namespace App\Http\Requests\ProvisionalReceipt;

use Illuminate\Foundation\Http\FormRequest;

class ProvisionalReceiptRequest extends FormRequest
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
            'apartment_id' => 'required|numeric',
            'config_id' => 'required|numeric',
            'customer_fullname' => 'required',
            'payment_type' => 'required',
            'customer_paid' => 'required'
        ];
    }
}