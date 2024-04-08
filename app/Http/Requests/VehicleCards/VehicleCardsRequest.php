<?php

namespace App\Http\Requests\VehicleCards;

use App\Traits\RequestRules;
use Illuminate\Foundation\Http\FormRequest;

class VehicleCardsRequest extends FormRequest
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
           'code'       => 'required',
           'bdc_vehicle_id'       => 'required',
       ];
       return $this->rulesByMethod($rules);
   }

    public function attributes()
    {
        return [
            'code' => 'Mã code',
            'bdc_vehicle_id' => 'Phương tiện',
        ];

    }
}
