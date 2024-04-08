<?php

namespace App\Repositories\BdcReceiptLogs;

use App\Repositories\Eloquent\Repository;

class ReceiptLogsRepository extends Repository {
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\BdcReceiptLogs\ReceiptLogs::class;
    }

    public function filterByBuildingId($buildingId, $key)
    {
        return $this->model->where(['bdc_building_id' => $buildingId, 'key' => $key])->orderBy('created_at', 'DESC');
    }

    public function filterBy($buildingId, $request)
    {
        return $this->model
        ->where(['bdc_building_id' => $buildingId])
        ->where(function($query) use ($request){
            
            if(isset($request->type) && $request->type != null)
            {
                $query->where('key', '=', $request->type);
            }
            if(isset($request->status) && $request->status != null)
            {
                $request->status == 1 ? $query->where('status', '=', 200) : $query->where('status', '<>', 200);
                
            }
            if(isset($request->bdc_apartment_id) && $request->bdc_apartment_id != null)
            {
                $query->where('bdc_apartment_id', '=', $request->bdc_apartment_id);
            }
        })
        ->orderBy('created_at', 'DESC');    
    }
}
