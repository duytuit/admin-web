<?php

namespace App\Http\Requests;

use App\Traits\RequestRules;
use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
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
            'type'    => 'required|in:article,event,voucher,feedback',
            'post_id' => 'required|exists:posts,id',
            'content' => 'required',
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
            'type'    => 'phân loại',
            'post_id' => 'bài viết',
            'content' => 'nội dung',
            'rating'  => 'đánh giá',
        ];
    }
}
