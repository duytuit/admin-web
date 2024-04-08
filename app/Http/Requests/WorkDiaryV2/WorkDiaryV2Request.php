<?php

namespace App\Http\Requests\WorkDiaryV2;

use App\Traits\RequestRules;
use Illuminate\Foundation\Http\FormRequest;

class WorkDiaryV2Request extends FormRequest
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
            'department_id'         => 'required',
            'task_name'	            => 'required',
            'description'		    => 'required',
            'supervisor'		    => 'required',
            //'work_shift_id'       => 'required',
            'created_by'		    => 'required',
            'user_infos'		    => 'required',
            //'sub_tasks'		        => 'required',
            //'due_date'            => 'required',
            'start_date'	        => 'required',
            'type'		            => 'required',
            'task_category_id'		=> 'required'
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
            'department_id'    => 'bộ phận',
            'work_shift_id'	    => 'ca làm việc',
            'task_name'	    => 'tên công việc',
            'description'		    => 'mô tả',
            'supervisor'		    => 'người theo dõi',
            'created_by'		    => 'người tạo',
            'user_infos'		    => 'người thực hiện',
            'sub_tasks'		    => 'công việc nhỏ',
            'due_date'		    => 'ngày kết thúc',
            'start_date'	    => 'ngày bắt đầu',
            'type'		    => 'kiểu công việc',
            'task_category_id'		    => 'danh mục'
        ];
    }
}