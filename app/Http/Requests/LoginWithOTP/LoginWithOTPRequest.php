<?php

namespace App\Http\Requests\LoginWithOTP;

use Illuminate\Foundation\Http\FormRequest;

class LoginWithOTPRequest extends FormRequest
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
            //
            'account'                    => 'required|max:191',
            'verifycode'                  => 'required',
        ];
    }
     /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
          'account'                        => 'code user',
          'verifycode'                      => 'mã xác thực',
        ];
    }
}
