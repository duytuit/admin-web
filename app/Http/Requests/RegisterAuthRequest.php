<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterAuthRequest extends FormRequest
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
            'cb_name'     => 'required|string',
            'cb_email'    => 'required|email|unique:b_o_customers',
            'cb_password' => 'required|string|min:6|max:10',
        ];
    }
}
