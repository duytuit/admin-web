<?php

namespace App\Repositories\ElectricMeter;

use App\Repositories\Eloquent\Repository;
use Illuminate\Support\Facades\DB;

class ElectricMeterRespository extends Repository
{



    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\BdcElectricMeter\ElectricMeter::class;
    }

    public function getList($building_id, $request)
    {
        $sql = "SELECT 
                `a`.`name`,
                `a`.`code`,
                `a`.`id` AS bdc_apartment_id,
                `b`.`bdc_building_id`,
                `b`.`id`,
                `b`.`cycle_name`,
                `b`.`images`,
                `b`.`created_at`,
                `b`.`updated_at`,
                `b`.`deleted_at`,
                `b`.`chi_so_dau`,
                `b`.`chi_so_cuoi`,
                `b`.`type`,
                `b`.`user_id`
            FROM
                bdc_apartments AS a
                    LEFT JOIN
                (SELECT 
                    `bdc_apartments`.`name`,
                        `bdc_apartments`.`code`,
                        `bdc_apartments`.`id` AS `bdc_apartment_id`,
                        `bdc_electric_meter`.`bdc_building_id`,
                        `bdc_electric_meter`.`id`,
                        `bdc_electric_meter`.`cycle_name`,
                        `bdc_electric_meter`.`images`,
                        `bdc_electric_meter`.`created_at`,
                        `bdc_electric_meter`.`updated_at`,
                        `bdc_electric_meter`.`deleted_at`,
                        `bdc_electric_meter`.`chi_so_dau`,
                        `bdc_electric_meter`.`chi_so_cuoi`,
                        `bdc_electric_meter`.`type`,
                        `bdc_electric_meter`.`user_id`
                FROM
                    `bdc_apartments`
                LEFT JOIN `bdc_electric_meter` ON `bdc_apartments`.`id` = `bdc_electric_meter`.`bdc_apartment_id`
                WHERE
                        `bdc_apartments`.`building_id` = $building_id";
        if (isset($request->type) && $request->type != null) {
            $type = $request->type;
            $sql .= " AND(`bdc_electric_meter`.`type` = '$type'
                        OR `bdc_electric_meter`.`type` IS NULL) ";
        }
        $sql .= " AND `bdc_electric_meter`.`deleted_at` IS NULL
                        AND `bdc_apartments`.`deleted_at` IS NULL";

        if (isset($request->cycle_name) && $request->cycle_name != null) {
            $cycle_name = $request->cycle_name;
            $sql .= " AND ( `bdc_electric_meter`.`cycle_name` = '$cycle_name'
                        OR `bdc_electric_meter`.`cycle_name` IS NULL)";
        }
        if (isset($request->bdc_apartment_id) && $request->bdc_apartment_id != null) {
            $bdc_apartment_id = $request->bdc_apartment_id;
            $sql .= " AND `bdc_apartments`.`id` = $bdc_apartment_id";
        }
        // if(isset($request->search) && $request->search != null ){
        //     $search = $request->search;
        //     $sql .= " AND `bdc_apartments`.`name` like '%$search%'";
        // }
        $sql .= " ORDER BY `bdc_electric_meter`.`updated_at` DESC) AS b ON a.id = b.bdc_apartment_id WHERE `a`.`building_id` = $building_id";
        if (isset($request->search) && $request->search != null) {
            $search = $request->search;
            $sql .= " AND `a`.`name` like '%$search%'";
        }
        if (isset($request->bdc_apartment_id) && $request->bdc_apartment_id != null) {
            $bdc_apartment_id = $request->bdc_apartment_id;
            $sql .= " AND `a`.`id` = $bdc_apartment_id";
        }
        $sql .= " AND `a`.`deleted_at` IS NULL";
        return  DB::select(DB::raw($sql));
    }
    public function getListApi($building_id, $request, $perPage, $offSet)
    {
        $sql = "SELECT 
                    `a`.`name`,
                    `a`.`code`,
                    `a`.`id` AS bdc_apartment_id,
                    `b`.`bdc_building_id`,
                    `b`.`id`,
                    `b`.`cycle_name`,
                    `b`.`images`,
                    `b`.`created_at`,
                    `b`.`updated_at`,
                    `b`.`deleted_at`,
                    `b`.`chi_so_dau`,
                    `b`.`chi_so_cuoi`,
                    `b`.`type`,
                    `b`.`user_id`
                FROM
                    bdc_apartments AS a
                        LEFT JOIN
                    (SELECT 
                        `bdc_apartments`.`name`,
                            `bdc_apartments`.`code`,
                            `bdc_apartments`.`id` AS `bdc_apartment_id`,
                            `bdc_electric_meter`.`bdc_building_id`,
                            `bdc_electric_meter`.`id`,
                            `bdc_electric_meter`.`cycle_name`,
                            `bdc_electric_meter`.`images`,
                            `bdc_electric_meter`.`created_at`,
                            `bdc_electric_meter`.`updated_at`,
                            `bdc_electric_meter`.`deleted_at`,
                            `bdc_electric_meter`.`chi_so_dau`,
                            `bdc_electric_meter`.`chi_so_cuoi`,
                            `bdc_electric_meter`.`type`,
                            `bdc_electric_meter`.`user_id`
                    FROM
                        `bdc_apartments`
                    LEFT JOIN `bdc_electric_meter` ON `bdc_apartments`.`id` = `bdc_electric_meter`.`bdc_apartment_id`
                    WHERE
                            `bdc_apartments`.`building_id` = $building_id";
        if (isset($request->type) && $request->type != null) {
            $type = $request->type;
            $sql .= " AND(`bdc_electric_meter`.`type` = '$type'
                            OR `bdc_electric_meter`.`type` IS NULL) ";
        }
        $sql .= " AND `bdc_electric_meter`.`deleted_at` IS NULL
                            AND `bdc_apartments`.`deleted_at` IS NULL";

        if (isset($request->cycle_name) && $request->cycle_name != null) {
            $cycle_name = substr($request->cycle_name, 0, 4) . sprintf("%'.02d", substr($request->cycle_name, 4, strlen($request->cycle_name)));
            $sql .= " AND ( `bdc_electric_meter`.`cycle_name` = '$cycle_name'
                            OR `bdc_electric_meter`.`cycle_name` IS NULL)";
        }
        if (isset($request->bdc_apartment_id) && $request->bdc_apartment_id != null) {
            $bdc_apartment_id = $request->bdc_apartment_id;
            $sql .= " AND `bdc_apartments`.`id` = $bdc_apartment_id";
        }
        // if(isset($request->search) && $request->search != null ){
        //     $search = $request->search;
        //     $sql .= " AND `bdc_apartments`.`name` like '%$search%'";
        // }
        $sql .= " ORDER BY `bdc_electric_meter`.`updated_at` DESC) AS b ON a.id = b.bdc_apartment_id WHERE `a`.`building_id` = $building_id";
        if (isset($request->search) && $request->search != null) {
            $search = $request->search;
            $sql .= " AND `a`.`name` like '%$search%'";
        }
        $sql .= " AND `a`.`deleted_at` IS NULL";
        $sql .= " limit $perPage offset $offSet";
        return  DB::select(DB::raw($sql));
    }

    public function getCycleName($buildingId)
    {
        return $this->model->where('bdc_building_id', $buildingId)->select('month_create')->groupBy('month_create')->orderBy('month_create', 'DESC')->pluck('month_create')->toArray();
    }

    public function getCycleNameAll($buildingId, $type)
    {
        return $this->model->where(['bdc_building_id'=> $buildingId,'type'=>$type])->select('month_create')->groupBy('month_create')->orderBy('month_create', 'DESC')->get();
    }

    public function getLastCycleName($buildingId)
    {
         $electric_meters = $this->model->where('bdc_building_id', $buildingId)->select('month_create')->groupBy('month_create')->orderBy('month_create', 'DESC')->first();
         return  $electric_meters ? $electric_meters->month_create : null;
    }

    public function countApartmentUseByTypeAndCycleName($buildingId, $cycle_name, $type)
    {
        return $this->model->where(['bdc_building_id'=> $buildingId,'month_create'=>$cycle_name,'type'=>$type])->count();
    }
    public function getListAll($input, $buildingId){
        return $this->model->where(['bdc_building_id'=>$buildingId])->where(function($query) use($input){
            $query->where('type',$input['type']);
            if($input['cycle_name']){
                $query->where('month_create',$input['cycle_name']);
            } 
            if(isset($input['bdc_apartment_id']) && $input['bdc_apartment_id'] != null){
                $query->where('bdc_apartment_id',$input['bdc_apartment_id']);
            } 
            if(isset($input['floor']) && $input['floor'] != null){
                $query->where('bdc_apartments.floor',  $input['floor']);
            } 
            if(isset($input['ip_place_id']) && $input['ip_place_id'] != null){
                $ip_place_id = $input['ip_place_id'];
                $query->whereHas('apartment',function($query) use($ip_place_id){
                       $query->where('building_place_id',$ip_place_id);
                });
            }
        })->join('bdc_apartments', 'bdc_apartments.id', '=', 'bdc_electric_meter.bdc_apartment_id')->whereNull('bdc_apartments.deleted_at')->orderBy('building_place_id')->orderBy('name')->select(['bdc_electric_meter.*']);
    }
}