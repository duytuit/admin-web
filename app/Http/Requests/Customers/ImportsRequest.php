<?php

namespace App\Http\Requests\Customers;

use App\Traits\RequestRules;
use Illuminate\Foundation\Http\FormRequest;

class ImportsRequest extends FormRequest
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
            'file_import' => 'required|file|mimes:xlsx,csv,xls',
        ];
        return $this->rulesByMethod($rules);
    }

    public function attributes()
    {
        return [
            'file_import' => 'file'
        ];
    }
}
