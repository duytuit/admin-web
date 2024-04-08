<?php

namespace App\Repositories\Building;

use App\Models\Asset\Asset;
use App\Models\Asset\AssetArea;
use App\Models\Asset\AssetCategory;
use App\Models\Asset\AssetDetail;
use App\Models\Asset\Floor;
use App\Models\Building\Building;
use App\Models\Promotion\Promotion;
use App\Models\Building\Urban;
use App\Models\Task\CheckList;
use App\Models\UserRequest\UserRequest;
use App\Models\V3\TaskCategory;
use App\Repositories\Eloquent\Repository;
use Illuminate\Support\Facades\Cache;

const BUILDING_USER = 1;

class BuildingRepository extends Repository {
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return Building::class;
    }

    function getActiveBuilding($id)
    {
        return $this->model->find($id);
    }

    function updateDepartmentIdAndDebitDate($id, $bdc_department_id, $debit_date)
    {
        return $this->model->where('id', $id)->update(['bdc_department_id' => $bdc_department_id, 'debit_date' => $debit_date]);
    }

    public function getBuildingOfUser($building)
    {
        return $this->model->where('id',$building)->get();
    }

    public function getBuildingByCompany($request)
    {
        return $this->model->where(function($query) use($request){
            $query->where('company_id',$request->company_id);
        })->orderBy('status','desc')->get();
    }

    public function getCompanyOfBuildingId($urban_id)
    {
        $urban =  Urban::find($urban_id);
        return @$urban->company->id;
    }

    public function getAllByIds($select,$ids)
        {
        return $this->model->select($select)->whereIn('id',$ids)->get()->toArray();
    }
    public function getInfo($ids)
    {
        return $this->model->with('buildingInfo','paymentInfo')->whereIn('id',$ids)->where('status',1)->get();
    }
    public function getCode($id){
        $code = $this->model->where('id',$id)->first();
        return $code->building_code;
    }

    public static function getInfoBuildingById($id)
    {
        $keyCache = "getInfoBuildingById_" . $id;
        return Cache::remember($keyCache, 10 ,function () use ($keyCache, $id) {
            $rs = Building::where([
                "id" => $id
            ])->first();
            if (!$rs) return null;
            return (object) $rs->toArray();
        });
    }
    public function getAssetArea(array $options = [],$building_id)
    {
        $default = [
            'select'   => '*',
            'where'    => [],
            'order_by' => 'id DESC',
            'per_page' => 20,
        ];

        $options = array_merge($default, $options);

        extract($options);

        $model = AssetArea::select($options['select']);

        if ($options['where']) {
            $model = $model->where($options['where']);
        }
        $model = $model->where('building_id',$building_id);
        return $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
    }
    public function getAssetCategory(array $options = [],$building_id)
    {
        $default = [
            'select'   => '*',
            'where'    => [],
            'order_by' => 'id DESC',
            'per_page' => 20,
        ];

        $options = array_merge($default, $options);

        extract($options);

        $model = AssetCategory::select($options['select']);

        if ($options['where']) {
            $model = $model->where($options['where']);
        }
        $model = $model->where('building_id',$building_id);
        return $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
    }

    public function getAssetDetail(array $options = [], $building_id, $type = null, $request = null)
    {
        $default = [
            'select'   => '*',
            'where'    => [],
            'order_by' => 'id DESC',
            'per_page' => 20,
        ];

        $options = array_merge($default, $options);

        extract($options);
        $model = AssetDetail::select($options['select']);

        if ($type == 'asset_name') {
               $model->join('asset_detail', 'asset_detail.id', '=', 'asset_area_info.asset_detail_id');
              if (isset($request->search) && $request->search != null) {
                $model = $model->where('asset_detail.name','like','%'.$request->search.'%');
              }
              if (isset($request->office_id) && $request->office_id != null) {
                $model = $model->where('asset_area_info.office_id',$request->office_id);
              }
            $model = $model->where('asset_area_info.building_id',$building_id);
        }else{
            if ($options['where']) {
                $model = $model->where($options['where']);
            }
             $model = $model->where('building_id',$building_id);
        }
        return $model->paginate($options['per_page']);
    }
    public function getAsset(array $options = [],$building_id)
    {
        $default = [
            'select'   => '*',
            'where'    => [],
            'order_by' => 'id DESC',
            'per_page' => 20,
        ];

        $options = array_merge($default, $options);

        extract($options);

        $model = Asset::select($options['select']);

        if ($options['where']) {
            $model = $model->where($options['where']);
        }
        $model = $model->where('building_id',$building_id);
        return $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
    }
    public function getFloor(array $options = [],$building_id)
    {
        $default = [
            'select'   => '*',
            'where'    => [],
            'order_by' => 'id DESC',
            'per_page' => 20,
        ];

        $options = array_merge($default, $options);

        extract($options);

        $model = Floor::select($options['select']);

        if ($options['where']) {
            $model = $model->where($options['where']);
        }
        $model = $model->where('building_id',$building_id);
        return $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
    }
    public function getCheckList(array $options = [],$building_id)
    {
        $default = [
            'select'   => '*',
            'where'    => [],
            'order_by' => 'id DESC',
            'per_page' => 20,
        ];

        $options = array_merge($default, $options);

        extract($options);

        $model = CheckList::select($options['select']);

        if ($options['where']) {
            $model = $model->where($options['where']);
        }
        $model = $model->where('building_id',$building_id);
        $model = $model->where('type',1);
        $model = $model->where('status',1);
        return $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
    }
    public function getPromotion(array $options = [],$building_id)
    {
        $default = [
            'select'   => '*',
            'where'    => [],
            'order_by' => 'id DESC',
            'per_page' => 20,
        ];

        $options = array_merge($default, $options);

        extract($options);

        $model = Promotion::select($options['select']);

        if ($options['where']) {
            $model = $model->where($options['where']);
        }
        $model = $model->where('building_id',$building_id);
        return $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
    }

    public function getUrban(array $options = [],$building_id)
    {
        $default = [
            'select'   => '*',
            'where'    => [],
            'order_by' => 'id DESC',
            'per_page' => 20,
        ];
        $options = array_merge($default, $options);
        extract($options);
        $model = Urban::select($options['select']);
        if ($options['where']) {
            $model = $model->where($options['where']);
        }
        $model = $model->where('building_id',$building_id);
        return $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
    }

    public function getCateTask(array $options = [],$building_id)
    {
        $default = [
            'select'   => '*',
            'where'    => [],
            'order_by' => 'id DESC',
            'per_page' => 20,
        ];
        $options = array_merge($default, $options);
        extract($options);
        $model = TaskCategory::select($options['select']);
        if ($options['where']) {
            $model = $model->where($options['where']);
        }
        $model = $model->where('building_id',$building_id);
        $model = $model->where('status',1);
        return $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
    }

}
