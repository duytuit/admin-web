<?php

namespace App\Repositories\BdcProgressive;

use App\Models\BdcProgressivePrice\ProgressivePrice;
use App\Repositories\Eloquent\Repository;
use Carbon\Carbon;
use const App\Repositories\Service\MANY_PRICE;
use const App\Repositories\Service\UTILITIES;
use Illuminate\Database\Eloquent\Builder;

class ProgressiveRepository extends Repository {
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\BdcProgressives\Progressives::class;
    }

    public function addProgressive($buildingId, $companyId, $request, ProgressivePrice $progressivePrice) {
        $progressiveInput = $this->model->create([
            'name' => $request['name'], 
            'description' => $request['description'],
            'building_id' => $buildingId,
            'company_id' => $companyId,
            'bdc_price_type_id' => $request['bdc_price_type_id'],
            'applicable_date' => $request['applicable_date'] ? Carbon::parse($request['applicable_date']):null,
            'bdc_service_id' => $request['bdc_service_id']
        ]);

        if ($request['bdc_price_type_id'] == 1 || $request['bdc_price_type_id'] == 3) { 
            $progressivePrice->create([
                'name' => $request['name'], 
                'from' => 0, 
                'to' => 0, 
                'price' => $request['price'], 
                'progressive_id' => $progressiveInput->id
            ]);
        }
        if ($request['bdc_price_type_id'] == 4) { 
            foreach($request['progressive']['option'] as $key => $value) {
                $progressivePrice->create([
                    'name' => $request['name'],
                    'from' => 0, 
                    'to' => 0, 
                    'quantity' => $request['progressive']['quantity'][$key], 
                    'period_quantity' => $request['progressive']['period_quantity'][$key], 
                    'date_quantity' =>$request['progressive']['date_quantity'][$key], 
                    'price' => $request['progressive']['price'][$key], 
                    'option' => $request['progressive']['option'][$key], 
                    'progressive_id' => $progressiveInput->id
                ]);
            }
        }
        if ($request['bdc_price_type_id'] == 2) { 
            foreach($request['progressive']['from'] as $key => $value) {
                $from = $request['progressive']['from'][$key];
                $to = $request['progressive']['to'][$key];
                $price = $request['progressive']['price'][$key];
                $name = $progressiveInput->name . '(' . $from . ' - ' . $to . ')';
                $progressivePrice->create([
                    'name' => $name, 
                    'from' => $from, 
                    'to' => $to, 
                    'price' => $price, 
                    'progressive_id' => $progressiveInput->id
                ]);
            }
        }
    }

    public function updateProgressive($id, $buildingId, $companyId, $request, ProgressivePrice $progressivePrice) {
        $this->model->where('id', $id)->update([
            'name' => $request['name'], 
            'description' => $request['description'],
            'building_id' => $buildingId,
            'company_id' => $companyId,
            'bdc_price_type_id' => $request['bdc_price_type_id'],
            'applicable_date' => $request['applicable_date'] ? Carbon::parse($request['applicable_date']):null,
            'bdc_service_id' => $request['bdc_service_id']
        ]);
        
        $progressivePrice->where('progressive_id', $id)->delete();

        if ($request['bdc_price_type_id'] == 1 || $request['bdc_price_type_id'] == 3) { 
            $progressivePrice->create([
                'name' => $request['name'], 
                'from' => 0, 
                'to' => 0, 
                'price' => $request['price'], 
                'progressive_id' =>$id
            ]);
        }
        if ($request['bdc_price_type_id'] == 4) { 
            foreach($request['progressive']['option'] as $key => $value) {
                $progressivePrice->create([
                    'name' => $request['name'],
                    'from' => 0, 
                    'to' => 0, 
                    'quantity' => $request['progressive']['quantity'][$key], 
                    'period_quantity' => $request['progressive']['period_quantity'][$key], 
                    'date_quantity' =>$request['progressive']['date_quantity'][$key], 
                    'price' => $request['progressive']['price'][$key], 
                    'option' => $request['progressive']['option'][$key], 
                    'progressive_id' =>$id
                ]);
            }
        }
        if ($request['bdc_price_type_id'] == 2) { 
            foreach($request['progressive']['from'] as $key => $value) {
                $from = $request['progressive']['from'][$key];
                $to = $request['progressive']['to'][$key];
                $price = $request['progressive']['price'][$key];
                $name = $request['name'] . '(' . $from . ' - ' . $to . ')';
                $progressivePrice->create([
                    'name' => $name, 
                    'from' => $from, 
                    'to' => $to, 
                    'price' => $price, 
                    'progressive_id' =>$id
                ]);
            }
        }
    }
    public function chooseManyPrice($buildingId)
    {
        // $company =\Auth::user()->company_staff->company->id;
        $manyPrice = $this->model->where(['building_id' => $buildingId])->whereHas('priceType', function (Builder $query) {
            $query->where('id', '=', MANY_PRICE)
                  ->orWhere('id', '=', UTILITIES);
        })->get();

        return $manyPrice;
    }

    public function getManyPrice()
    {
        $company = \Auth::user()->company_staff->company->id;
        $manyPrice = $this->model->where(['company_id' => $company])->whereHas('priceType', function (Builder $query) {
            $query->where('id', '=', MANY_PRICE);
        })->get();
        $many = $manyPrice->pluck('name','id')->toArray();
        return $many;
    }

    public function findByBuildingId($buildingId)
    {
        return $this->model->where(['building_id' => $buildingId])->orderBy('updated_at','desc');
    }
}
