<?php

namespace App\Repositories\BdcDebitLogs;

use App\Repositories\Eloquent\Repository;
use Carbon\Carbon;

class DebitLogsRepository extends Repository {
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\BdcDebitLogs\DebitLogs::class;
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
            if(isset($request->bdc_service_id) && $request->bdc_service_id != null)
            {
                $query->where('bdc_service_id',$request->bdc_service_id);
            }
            if(isset($request->cycle_name) && $request->cycle_name != null)
            {
                $query->where('cycle_name',$request->cycle_name);
            }
            if (isset($request->from_date) && $request->from_date !=null && isset($request->to_date) && $request->to_date  !=null ) {
                $from_date = Carbon::parse($request->from_date)->format('Y-m-d');
                $to_date   = Carbon::parse($request->to_date)->format('Y-m-d');

                $query->whereDate('created_at', '>=', $from_date);
                $query->whereDate('created_at', '<=', $to_date);
            }
        })
        ->orderBy('created_at', 'DESC');    
    }
}
