<?php

namespace App\Repositories\Building;

use App\Models\Building\Building;
use App\Models\Building\BuildingPlace;
use App\Repositories\Eloquent\Repository;
use Illuminate\Support\Facades\Cache;


class BuildingPlaceRepository extends Repository {
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return BuildingPlace::class;
    }
    public function searchBy($buiding_id,$request='',$where=[],$perpage = 20)
    {
        if (!empty($request->name)) {
            $where[] = ['name', 'Like', "%{$request->name}%"];
        }

        if (!empty($request->mobile)) {
            $where[] = ['mobile', '=', $request->mobile];
        }
        if (!empty($request->email)) {
            $where[] = ['email', '=', $request->email];
        }

        if ($request->status != null && $request->status != 'false') {
            $where[] = ['status', '=', $request->status];
        }

        $default = [
            'select'   => '*',
            'where'    => $where,
            'order_by' => 'id DESC',
            'per_page' => $perpage,
        ];

        $options = array_merge($default, $where);

        extract($options);

        $model = $this->model->select($options['select']);

        if ($options['where']) {
            $model = $model->where($options['where']);
        }
        $model = $model->where('bdc_building_id',$buiding_id);
        $list_search = $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
        return $list_search;
    }
    public function findById($id)
    {
        return $this->model->where('id', $id)->first();
    }
    public function searchByAll(array $options = [],$building_id)
    {
        $default = [
            'select'   => '*',
            'where'    => [],
            'order_by' => 'id DESC',
            'per_page' => 20,
        ];

        $options = array_merge($default, $options);
        extract($options);

        $model = $this->model->select($options['select']);

        if ($options['where']) {
            $model = $model->where($options['where']);
        }
        $model = $model->where('bdc_building_id',$building_id);
        return $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
    }
    public function getCode($building_id,$id){
        $code = $this->model->where('bdc_building_id',$building_id)->where('id',$id)->first();
        return @$code->code;
    }
    public function searchByName($building_id,$name){
        return $this->model->where(['bdc_building_id'=>$building_id,'name'=>$name])->first();
    }
    public function getId($building_id,$code){
        $code = $this->model->where('bdc_building_id',$building_id)->where('code',$code)->first();
        return $code->id;
    }
    public function getDataExport(array $select,$building_id)
    {
        return $this->model->select($select)->where('bdc_building_id',$building_id)->get();
    }

    public function searchByEmail($key,$building_id)
    {
        $options = [
            'select'   => '*',
            'where'    => [],
            'order_by' => 'id DESC',
            'per_page' => 20,
        ];
        $model = $this->model->select($options['select']);
        if($key){
            $model= $model->where('email','like','%'.$key.'%');
        }
        $model= $model->where('bdc_building_id',$building_id);
        return $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
    }
    public function deleteSelects($request)
    {
        return $this->model->whereIn('id',$request->ids)->delete();
    }
    public function findByCode($code,$building_id)
    {
        return $this->model->where('code', $code)->where('bdc_building_id',$building_id)->first();
    }

    public static function getInfoBuildingPlaceById($id)
    {
        $keyCache = "getInfoBuildingPlaceById_" . $id;
        return Cache::remember($keyCache, 10 ,function () use ($keyCache, $id) {
            $rs = BuildingPlace::where([
                "id" => $id
            ])->first();
            if (!$rs) return null;
            return (object) $rs->toArray();
        });
    }
}
