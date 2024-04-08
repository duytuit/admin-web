<?php

namespace App\Http\Requests\Feedback;

use App\Traits\RequestRules;
use Illuminate\Foundation\Http\FormRequest;

class FeedbackRequest extends FormRequest
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
            'type'       => 'required',
        ];
        return $this->rulesByMethod($rules);
    }

    public function attributes()
    {
        return [
            'title' => 'Tiêu đề',
        ];
    }
}
