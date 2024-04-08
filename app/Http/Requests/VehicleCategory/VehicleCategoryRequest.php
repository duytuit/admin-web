<?php

namespace App\Http\Requests\VehicleCategory;

use App\Traits\RequestRules;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class VehicleCategoryRequest extends FormRequest
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
    public function rules(Request $request)
    {
        if(isset($request->ngay_chuyen_doi) && $request->ngay_chuyen_doi != null) {
            $rules = [
                'name'                  => 'required',
                'ngay_chuyen_doi' => 'numeric|min:1|max:30',
            ];
        }else{
            $rules = [
                'name'       => 'required',
            ];
        }
        
        return $this->rulesByMethod($rules);
    }
    public function attributes()
    {
        return [
            'name' => 'Tên danh mục',
            'ngay_chuyen_doi.numeric' => 'Ngày chuyển đổi phải là số',
            'ngay_chuyen_doi.min' => 'Ngày chuyển đổi không dưới 1',
            'ngay_chuyen_doi.max' => 'Ngày chuyển đổi không quá 30',
        ];

    }
}
