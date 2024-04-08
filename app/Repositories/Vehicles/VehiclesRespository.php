<?php


namespace App\Repositories\Vehicles;

//use App\Repositories\Contracts\RepositoryInterface;

use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Models\Vehicles\Vehicles;
use App\Repositories\Eloquent\Repository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class VehiclesRespository extends Repository
{


    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\Vehicles\Vehicles::class;
    }
    public function findById($id)
    {
        return $this->model->findOrFail($id);
    }
    public function findAllBy($colums = 'id', $id)
    {
        return $this->model->where($colums, $id)->get();
    }

    public function getVehicleInApartment($apartmentId)
    {
        return $this->model->where(['bdc_apartment_id' => $apartmentId])->with('bdcVehiclesCategory')->get();
    }

    public function getOne($colums = 'id', $id)
    {
        $row = $this->model->where($colums, $id)->first();
        $row->load('bdcVehiclesCategory');
        $row->load('bdcApartment');
        return $row;
    }

    public function find_bdc_apartment_service_price_id($apartment_id,$bdc_progressive_price_id)
    {
        return $this->model->where(['bdc_apartment_id'=>$apartment_id])->whereHas('apartmentServicePrices',function($query)use ($bdc_progressive_price_id){
            $query->where('id', $bdc_progressive_price_id);
        })->first();
    }

    public function searchVehicle($buiding_id,$request, $where = [], $perpage = 20)
    {

        $default = [
            'select' => '*',
            'where' => $where,
            'order_by' => 'id DESC',
            'per_page' => $perpage,
        ];

        $options = array_merge($default, $where);
        extract($options);
        $model = $this->model->withTrashed()->select($options['select'])->distinct('vehicle_category_id');

        if (!empty($request->keyword)) {
            $model->Where(function($query) use ($request){
                $query->orWhere('name', 'like', '%' . $request->keyword . '%');
                $query->orWhere('number', 'like', '%' . $request->keyword . '%');
                $query->orWhere('description', 'like', '%' . $request->keyword . '%');
            });
        }
        if (!empty($request->apartment)) {
            $model = $model->where('bdc_apartment_id', '=', $request->apartment);
        }
        $model->whereHas('bdcVehiclesCategory', function ($query) use ($request) {
                $query->whereNull('bdc_service_id');
                if (!empty($request->cate)) {
                     $query->where('id', '=', $request->cate);
                }
            });
        $model->whereHas('bdcApartment', function ($query) use ($request,$buiding_id) {
            $query->where('building_id', '=', $buiding_id);
            if (!empty($request->place_id)) {
                $query->where('building_place_id', $request->place_id);
            }
        });

        $list_search = $model->orderBy('deleted_at')->orderByRaw($options['order_by'])->paginate($options['per_page']);

        return $list_search;
    }
    public function searchVehicle_v2($buiding_id,$request, $where = [], $perpage = 20)
    {

        $default = [
            'select' => '*',
            'where' => $where,
            'order_by' => 'id DESC',
            'per_page' => $perpage,
        ];

        $options = array_merge($default, $where);
        extract($options);
        $model = $this->model->withTrashed();

        if (!empty($request->keyword)) {
            $model->Where(function($query) use ($request){
                $query->orWhere('name', 'like', '%' . $request->keyword . '%');
                $query->orWhere('number', 'like', '%' . $request->keyword . '%');
                $query->orWhere('description', 'like', '%' . $request->keyword . '%');
            });
        }
        if (!empty($request->apartment)) {
            $model = $model->where('bdc_apartment_id', '=', $request->apartment);
        }
        $model->whereHas('bdcVehiclesCategory', function ($query) use ($request) {
            $query->whereNotNull('bdc_service_id');
            if (!empty($request->cate)) {
                $query->where('id', '=', $request->cate);
            }
        });
        $model->whereHas('bdcApartment', function ($query) use ($request,$buiding_id) {
            $query->where('building_id', '=', $buiding_id);
            if (!empty($request->place_id)) {
                $query->where('building_place_id', $request->place_id);
            }
        });

        if(isset($request->from_date) && $request->from_date !=null){
            $from_date = Carbon::parse($request->from_date)->format('Y-m-d');
            $model->whereDate('created_at','>=',$from_date);
        }
        if(isset($request->to_date) && $request->to_date !=null){
            $to_date = Carbon::parse($request->to_date)->format('Y-m-d');
            $model->whereDate('created_at','<=',$to_date);
        }

        $list_search = $model->orderBy('deleted_at')->orderByRaw($options['order_by']);

        return $list_search;
    }
    public function ChoppyByTypeVehicle($buildingId,$from_date,$to_date,$request)
    {
        $sql = "SELECT a_tb1.id,a_tb1.name,
        (SELECT count(v_tb3.id) from(SELECT id,deleted_at,created_at,bdc_apartment_id,vehicle_category_id,status,updated_at
            FROM bdc_vehicles) as v_tb3 WHERE a_tb1.id = v_tb3.bdc_apartment_id and EXISTS
        (SELECT c_tb1.id FROM bdc_vehicles_category as c_tb1 WHERE c_tb1.id = v_tb3.vehicle_category_id AND c_tb1.type = 3 and c_tb1.deleted_at is null) and v_tb3.deleted_at is null and v_tb3.created_at < '$from_date') as dau_ky_vao_o_to,
        (SELECT count(v_tb3.id) from(SELECT id,deleted_at,created_at,bdc_apartment_id,vehicle_category_id,status,updated_at
            FROM bdc_vehicles) as v_tb3 WHERE a_tb1.id = v_tb3.bdc_apartment_id and EXISTS
        (SELECT c_tb1.id FROM bdc_vehicles_category as c_tb1 WHERE c_tb1.id = v_tb3.vehicle_category_id AND c_tb1.type = 3 and c_tb1.deleted_at is null) and v_tb3.`status` = 0 and v_tb3.deleted_at is null and v_tb3.updated_at < '$from_date') as dau_ky_ra_o_to,
        (SELECT count(v_tb3.id) from(SELECT id,deleted_at,created_at,bdc_apartment_id,vehicle_category_id,status,updated_at
            FROM bdc_vehicles) as v_tb3 WHERE a_tb1.id = v_tb3.bdc_apartment_id and EXISTS
        (SELECT c_tb1.id FROM bdc_vehicles_category as c_tb1 WHERE c_tb1.id = v_tb3.vehicle_category_id AND c_tb1.type = 3 and c_tb1.deleted_at is null) and v_tb3.deleted_at is null and v_tb3.created_at >= '$from_date' and v_tb3.created_at <= '$to_date 23:59:59') as trong_ky_vao_o_to,
        (SELECT count(v_tb3.id) from(SELECT id,deleted_at,created_at,bdc_apartment_id,vehicle_category_id,status,updated_at
            FROM bdc_vehicles) as v_tb3 WHERE a_tb1.id = v_tb3.bdc_apartment_id and EXISTS
        (SELECT c_tb1.id FROM bdc_vehicles_category as c_tb1 WHERE c_tb1.id = v_tb3.vehicle_category_id AND c_tb1.type = 3 and c_tb1.deleted_at is null) and v_tb3.`status` = 0 and v_tb3.deleted_at is null and v_tb3.updated_at >= '$from_date' and v_tb3.updated_at <= '$to_date 23:59:59') as trong_ky_ra_o_to,
        (SELECT count(v_tb3.id) from(SELECT id,deleted_at,created_at,bdc_apartment_id,vehicle_category_id,status,updated_at
            FROM bdc_vehicles) as v_tb3 WHERE a_tb1.id = v_tb3.bdc_apartment_id and EXISTS
        (SELECT c_tb1.id FROM bdc_vehicles_category as c_tb1 WHERE c_tb1.id = v_tb3.vehicle_category_id AND c_tb1.type = 2 and c_tb1.deleted_at is null) and v_tb3.deleted_at is null and v_tb3.created_at < '$from_date') as dau_ky_vao_xe_may,
        (SELECT count(v_tb3.id) from(SELECT id,deleted_at,created_at,bdc_apartment_id,vehicle_category_id,status,updated_at
            FROM bdc_vehicles) as v_tb3 WHERE a_tb1.id = v_tb3.bdc_apartment_id and EXISTS
        (SELECT c_tb1.id FROM bdc_vehicles_category as c_tb1 WHERE c_tb1.id = v_tb3.vehicle_category_id AND c_tb1.type = 2 and c_tb1.deleted_at is null) and v_tb3.`status` = 0 and v_tb3.deleted_at is null and v_tb3.updated_at < '$from_date') as dau_ky_ra_xe_may,
        (SELECT count(v_tb3.id) from(SELECT id,deleted_at,created_at,bdc_apartment_id,vehicle_category_id,status,updated_at
            FROM bdc_vehicles) as v_tb3 WHERE a_tb1.id = v_tb3.bdc_apartment_id and EXISTS
        (SELECT c_tb1.id FROM bdc_vehicles_category as c_tb1 WHERE c_tb1.id = v_tb3.vehicle_category_id AND c_tb1.type = 2 and c_tb1.deleted_at is null) and v_tb3.deleted_at is null and v_tb3.created_at >= '$from_date' and v_tb3.created_at <= '$to_date 23:59:59') as trong_ky_vao_xe_may,
        (SELECT count(v_tb3.id) from(SELECT id,deleted_at,created_at,bdc_apartment_id,vehicle_category_id,status,updated_at
            FROM bdc_vehicles) as v_tb3 WHERE a_tb1.id = v_tb3.bdc_apartment_id and EXISTS
        (SELECT c_tb1.id FROM bdc_vehicles_category as c_tb1 WHERE c_tb1.id = v_tb3.vehicle_category_id AND c_tb1.type = 2 and c_tb1.deleted_at is null) and v_tb3.`status` = 0 and v_tb3.deleted_at is null and v_tb3.updated_at >= '$from_date' and v_tb3.updated_at <= '$to_date 23:59:59') as trong_ky_ra_xe_may,
        (SELECT count(v_tb3.id) from(SELECT id,deleted_at,created_at,bdc_apartment_id,vehicle_category_id,status,updated_at
            FROM bdc_vehicles) as v_tb3 WHERE a_tb1.id = v_tb3.bdc_apartment_id and EXISTS
        (SELECT c_tb1.id FROM bdc_vehicles_category as c_tb1 WHERE c_tb1.id = v_tb3.vehicle_category_id AND c_tb1.type = 1 and c_tb1.deleted_at is null) and v_tb3.deleted_at is null and v_tb3.created_at < '$from_date') as dau_ky_vao_xe_dap,
        (SELECT count(v_tb3.id) from(SELECT id,deleted_at,created_at,bdc_apartment_id,vehicle_category_id,status,updated_at
            FROM bdc_vehicles) as v_tb3 WHERE a_tb1.id = v_tb3.bdc_apartment_id and EXISTS
        (SELECT c_tb1.id FROM bdc_vehicles_category as c_tb1 WHERE c_tb1.id = v_tb3.vehicle_category_id AND c_tb1.type = 1 and c_tb1.deleted_at is null) and v_tb3.`status` = 0 and v_tb3.deleted_at is null and v_tb3.updated_at < '$from_date') as dau_ky_ra_xe_dap,
        (SELECT count(v_tb3.id) from(SELECT id,deleted_at,created_at,bdc_apartment_id,vehicle_category_id,status,updated_at
            FROM bdc_vehicles) as v_tb3 WHERE a_tb1.id = v_tb3.bdc_apartment_id and EXISTS
        (SELECT c_tb1.id FROM bdc_vehicles_category as c_tb1 WHERE c_tb1.id = v_tb3.vehicle_category_id AND c_tb1.type = 1 and c_tb1.deleted_at is null) and v_tb3.deleted_at is null and v_tb3.created_at >= '$from_date' and v_tb3.created_at <= '$to_date 23:59:59') as trong_ky_vao_xe_dap,
        (SELECT count(v_tb3.id) from(SELECT id,deleted_at,created_at,bdc_apartment_id,vehicle_category_id,status,updated_at
            FROM bdc_vehicles) as v_tb3 WHERE a_tb1.id = v_tb3.bdc_apartment_id and EXISTS
        (SELECT c_tb1.id FROM bdc_vehicles_category as c_tb1 WHERE c_tb1.id = v_tb3.vehicle_category_id AND c_tb1.type = 1 and c_tb1.deleted_at is null) and v_tb3.`status` = 0 and v_tb3.deleted_at is null and v_tb3.updated_at >= '$from_date' and v_tb3.updated_at <= '$to_date 23:59:59') as trong_ky_ra_xe_dap,
        (SELECT count(v_tb3.id) from(SELECT id,deleted_at,created_at,bdc_apartment_id,vehicle_category_id,status,updated_at
            FROM bdc_vehicles) as v_tb3 WHERE a_tb1.id = v_tb3.bdc_apartment_id and EXISTS
        (SELECT c_tb1.id FROM bdc_vehicles_category as c_tb1 WHERE c_tb1.id = v_tb3.vehicle_category_id AND c_tb1.type = 4 and c_tb1.deleted_at is null) and v_tb3.deleted_at is null and v_tb3.created_at < '$from_date') as dau_ky_vao_xe_dien,
        (SELECT count(v_tb3.id) from(SELECT id,deleted_at,created_at,bdc_apartment_id,vehicle_category_id,status,updated_at
            FROM bdc_vehicles) as v_tb3 WHERE a_tb1.id = v_tb3.bdc_apartment_id and EXISTS
        (SELECT c_tb1.id FROM bdc_vehicles_category as c_tb1 WHERE c_tb1.id = v_tb3.vehicle_category_id AND c_tb1.type = 4 and c_tb1.deleted_at is null) and v_tb3.`status` = 0 and v_tb3.deleted_at is null and v_tb3.updated_at < '$from_date') as dau_ky_ra_xe_dien,
        (SELECT count(v_tb3.id) from(SELECT id,deleted_at,created_at,bdc_apartment_id,vehicle_category_id,status,updated_at
            FROM bdc_vehicles) as v_tb3 WHERE a_tb1.id = v_tb3.bdc_apartment_id and EXISTS
        (SELECT c_tb1.id FROM bdc_vehicles_category as c_tb1 WHERE c_tb1.id = v_tb3.vehicle_category_id AND c_tb1.type = 4 and c_tb1.deleted_at is null) and v_tb3.deleted_at is null and v_tb3.created_at >= '$from_date' and v_tb3.created_at <= '$to_date 23:59:59') as trong_ky_vao_xe_dien,
        (SELECT count(v_tb3.id) from(SELECT id,deleted_at,created_at,bdc_apartment_id,vehicle_category_id,status,updated_at
            FROM bdc_vehicles) as v_tb3 WHERE a_tb1.id = v_tb3.bdc_apartment_id and EXISTS
        (SELECT c_tb1.id FROM bdc_vehicles_category as c_tb1 WHERE c_tb1.id = v_tb3.vehicle_category_id AND c_tb1.type = 4 and c_tb1.deleted_at is null) and v_tb3.`status` = 0 and v_tb3.deleted_at is null and v_tb3.updated_at >= '$from_date' and v_tb3.updated_at <= '$to_date 23:59:59') as trong_ky_ra_xe_dien
        FROM(SELECT * FROM bdc_apartments WHERE building_id = $buildingId";
        if (isset($request->place_id) && $request->place_id !=null) {
            $place_id = $request->place_id;
            $sql.=" AND bdc_apartment_group_id = $place_id";
        }
        if (isset($request->apartment) && $request->apartment !=null) {
            $apartment = $request->apartment;
            $sql.=" AND id = $apartment";
        }
        $sql.=" AND deleted_at is null) as a_tb1";
        return DB::table(DB::raw("($sql) as tb1"));

    }
    public function exportVehicle($buiding_id,$request, $where = [], $perpage = 20)
    {

        $default = [
            'select' => '*',
            'where' => $where,
            'order_by' => 'id DESC',
            'per_page' => $perpage,
        ];

        $options = array_merge($default, $where);
        extract($options);
        $model = $this->model->select($options['select'])->distinct('vehicle_category_id');

        if (!empty($request->keyword)) {
            $model->Where(function($query) use ($request){
                $query->orWhere('name', 'like', '%' . $request->keyword . '%');
                $query->orWhere('number', 'like', '%' . $request->keyword . '%');
                $query->orWhere('description', 'like', '%' . $request->keyword . '%');
            });
        }
        if (!empty($request->apartment)) {
            $model = $model->where('bdc_apartment_id', '=', $request->apartment);
        }
        $model->whereHas('bdcVehiclesCategory', function ($query) use ($request) {
            $query->whereNull('bdc_service_id');
            if (!empty($request->cate)) {
                $query->where('id', '=', $request->cate);
            }
        });
        $where[] = $model->whereHas('bdcApartment', function ($query) use ($request,$buiding_id) {
            $query->where('building_id', '=', $buiding_id);
            if (!empty($request->place_id)) {
                $query->where('building_place_id', $request->place_id);
            }
        });

        $list_search = $model->orderBy('updated_at','asc')->orderByRaw($options['order_by'])->get();

        return $list_search;
    }

    public function exportVehicle_v2($buiding_id,$request, $where = [], $perpage = 20)
    {

        $default = [
            'select' => '*',
            'where' => $where,
            'order_by' => 'id DESC',
            'per_page' => $perpage,
        ];

        $options = array_merge($default, $where);
        extract($options);
        $model = $this->model->select($options['select'])->distinct('vehicle_category_id');

        if (!empty($request->keyword)) {
            $model->Where(function($query) use ($request){
                $query->orWhere('name', 'like', '%' . $request->keyword . '%');
                $query->orWhere('number', 'like', '%' . $request->keyword . '%');
                $query->orWhere('description', 'like', '%' . $request->keyword . '%');
            });
        }
        if (!empty($request->apartment)) {
            $model = $model->where('bdc_apartment_id', '=', $request->apartment);
        }
        $model->whereHas('bdcVehiclesCategory', function ($query) use ($request) {
            $query->whereNotNull('bdc_service_id');
            if (!empty($request->cate)) {
                $query->where('id', '=', $request->cate);
            }
        });
        $where[] = $model->whereHas('bdcApartment', function ($query) use ($buiding_id) {
            $query->where('building_id', '=', $buiding_id);
        });

        $list_search = $model->orderBy('updated_at','asc')->orderByRaw($options['order_by'])->get();
        $list_search->load('bdcVehiclesCategory');
        $list_search->load('bdcApartment');
        $list_search->load('bdcVehicleCard');
        $list_search->load('updated_user');

        return $list_search;
    }

    public function findByType($attr, $id)
    {
        $check = $this->model->where($attr, $id)->first();
        $type = isset($check->type) ? $check->type : 0;
        if ($type == 1) {
            return $check->bdc_apartment_id;
        }
        return 0;
    }
    public function searchByAll(array $options = [])
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

        return $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
    }
    public function getByReqVcard($request)
    {
        $where = [];
        if ($request->bdc_apartment_id) {
            $where[] = ['bdc_apartment_id', '=', $request->bdc_apartment_id];
        }
        if ($request->vehicle_category_id) {
            $where[] = ['vehicle_category_id', '=', $request->vehicle_category_id];
        }
        if ($request->number) {
            $where[] = ['number', 'like', '%' . $request->number . '%'];
        }

        $default = [
            'select' => '*',
            'where' => $where,
            'order_by' => 'id DESC',
            'per_page' => 20,
        ];


        $model = $this->model->select($default['select']);

        if ($default['where']) {
            $model = $model->where($default['where']);
        }

        return $model->first();
    }

    public function vehicleAllByNumber()
    {
        return $this->model->pluck('number')->toArray();
    }
    public function insert(array $data) {
        return $this->model->insert($data);
    }

    public function getDataFile($file)
    {
        $path = $file->getRealPath();

        $excel_data = Excel::load($path)->get();

        storage_path('upload', $file->getClientOriginalName());

        $url = [
            'name' => $file->getClientOriginalName(),
            'uuid' => (string) \Webpatser\Uuid\Uuid::generate(),
        ];

        if ($excel_data->count()) {
            $excel_vehicle = $this->unsetVehicle($excel_data);
            $data_vehicle = $this->vehicleExelData($excel_vehicle,$url);
            $save = $this->vehicleDataSave($data_vehicle);
        }

        return $save;
    }

    public function unsetVehicle($vehicle)
    {
        $duplicate=[];
        for ($i = 0; $i < count($vehicle) - 1; $i++) {
            for ($j = $i + 1; $j < count($vehicle)-1; $j++) {
                if (!empty($vehicle[$j]) && $vehicle[$i]['name'] !== null && ($vehicle[$i]['name'] == $vehicle[$j]['name'])) {
                    $duplicate[$j] = $vehicle[$j];
                    unset($vehicle[$j]);
                } elseif (!empty($vehicle[$j]) &&  $vehicle[$i]['number'] !== null && ($vehicle[$i]['number'] == $vehicle[$j]['number'])) {
                    $duplicate[$j] = $vehicle[$j];
                    unset($vehicle[$j]);
                }
            }
        }
        $vehecl = [];
        for ($i = 0; $i <= count($vehicle); $i++) {
            if(isset($vehicle[$i])){
                if ( $vehicle[$i]['index'] == null &&
                    $vehicle[$i]['apartment_name'] == null &&
                    $vehicle[$i]['name'] == null &&
                    $vehicle[$i]['number'] == null &&
                    $vehicle[$i]['description'] == null &&
                    $vehicle[$i]['type'] == null) {
                    continue;
                }
                $vehecl[]=$vehicle[$i];
            }
        }

        $data = [
            'data' => $vehecl,
            'duplicate' => $duplicate,
        ];
        return $data;
    }

    public function vehicleExelData($vehicles,$url)
    {
        $check_vehicle = $this->vehicleAllByNumber();
        $has_ap=[];$fail_ap=[];$new_ap=[];
        foreach ($vehicles['data'] as $key => $vh) {
            if (!in_array($vh->number, $check_vehicle)) {
                if($vh->index && $vh->name && $vh->number && $vh->description && $vh->apartment_name && $vh->type){
                    $new_ap[] = [
                        'index'=> $vh->index,
                        'name' => $vh->name,
                        'number' => $vh->number,
                        'description' => $vh->description,
                        'apartment_name' => $vh->apartment_name,
                        'building_place' => $vh->building_place,
                        'type' => $vh->type,
                    ];
                }else{
                    $fail_ap[] = [
                        'index'=> $vh->index,
                        'name' => $vh->name,
                        'number' => $vh->number,
                        'description' => $vh->description,
                        'apartment_name' => $vh->apartment_name,
                        'building_place' => $vh->building_place,
                        'type' => $vh->type,
                    ];
                }

            } else {
                $has_ap[] =  [
                    'index'=> $vh->index,
                    'name' => $vh->name,
                    'number' => $vh->number,
                    'description' => $vh->description,
                    'apartment_name' => $vh->apartment_name,
                    'building_place' => $vh->building_place,
                    'type' => $vh->type,
                ];
            }
        }

        if (!empty($has_ap)) {
            $messages[]= [
                'messages' => 'Có ' . count($has_ap) . ' phương tiện đã có trên hệ thống',
                'data'     => $has_ap,
            ];
        }

        if(!empty($vehicles['duplicate'])){
            $dlc[]= json_decode(reset($vehicles['duplicate']), True);
            $messages[]= [
                'messages' => 'Có ' . count($vehicles['duplicate']) . ' phương tiện bị trùng trong file',
                'data'     => $dlc,
            ];
        }
        if(!empty($new_ap)){
            $messages[]= [
                'messages' => 'Có ' . count($new_ap) . ' phương tiện đầy đủ dữ liệu',
                'data'     => $new_ap,
            ];
        }
        if(!empty($fail_ap)){
            $messages[]= [
                'messages' => 'Có ' . count($fail_ap) . ' phương tiện bị thiếu dữ liệu',
                'data'     => $fail_ap
            ];
        }
        $data['messages'] = $messages;
        $data_new = [
            'data_vh' =>$new_ap,
            'url_file'  => $url,
            'duplicate' => $vehicles['duplicate']??[],
        ];
        $data['data'] = $data_new;
        return $data;
    }
    public function vehicleDataSave($dataExel)
    {
        $time = Carbon::now();
        $data_ap=[];
        if ($dataExel['data']['data_vh'] && !(session()->get('errors_user'))) {
            foreach ($dataExel['data']['data_vh'] as $index => $vh) {
                $data_ap[] = [
                    'name'  => $vh['name'],
                    'number' => $vh['number'],
                    'description' => $vh['description'],
                    'vehicle_category_id' => $vh['type'],
                    'building_place' => $vh['building_place'],
                    'bdc_apartment_id' => 0,
                    'created_at'     => $time,
                    'updated_at'     => $time,
                ];
            }

        }
        $dataExel['data'] = array_merge($dataExel['data'],['vehicles' =>$data_ap ?? '']);
        return $dataExel;
    }
    public function countItem($building = 0)
    {
        return $this->model->whereHas('bdcApartment', function ($query) use ($building) { $query->where('building_id', '=', $building); })->count();
    }

    public function findByNumber($number)
    {
        return $this->model->where('number','=',$number)->first();
    }
    public function checkNumberExit($number, $apartment_id){
        return $this->model->where('number','=', $number)->where('bdc_apartment_id', $apartment_id)->first();
    }
    public function checkNumberid($number,$buiding_id, $id=0){
        $check = $this->model->where('number','=', $number)->where('status',1);

        if($id>0){
            $check =  $check->whereNotIn('id',[$id]);
        }
        $check =  $check->whereHas('bdcApartment', function ($query) use ($buiding_id) {
            $query->where('building_id', '=', $buiding_id);
        });
        $check = $check->first();
        return $check;
    }

    public function deleteAt($request)
    {
        $ids = $request->input('ids', []);

        // chuyển sang kiểu array
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $list = [];
        foreach ($ids as $id) {
            $check_destroy = ApartmentServicePrice::join('bdc_debit_detail', 'bdc_debit_detail.bdc_apartment_service_price_id', '=', 'bdc_apartment_service_price.id')
                                                                 ->whereNull('bdc_debit_detail.deleted_at')
                                                                 ->whereNull('bdc_apartment_service_price.deleted_at')
                                                                 ->where('bdc_apartment_service_price.bdc_vehicle_id', $id)->count();
            if($check_destroy > 0){ // có phát sinh phí dịch vụ nên không cho xóa
                continue;
            }
            $list[] = (int) $id;
        }

        $apartments = [];

        foreach ($list as $id) {
            $vehicle = Vehicles::find($id);
            if (!empty($vehicle)) {
                if (!isset($apartments[$vehicle->bdc_apartment_id])) {
                    $apartments[$vehicle->bdc_apartment_id] = [$vehicle->vehicle_category_id];
                }
                else {
                    if (!in_array($vehicle->vehicle_category_id, $apartments[$vehicle->bdc_apartment_id])) {
                        array_push($apartments[$vehicle->bdc_apartment_id], $vehicle->vehicle_category_id);
                    }
                }

            }
        }


        $bdc_vehicles = $this->model->whereIn('id', $list)->update(['updated_by'=>Auth::user()->id]);

        $this->model->whereIn('id', $list)->delete();

        $bdc_apartment_service_price = ApartmentServicePrice::whereIn('bdc_vehicle_id',$list)->update(['updated_by'=>Auth::user()->id]);

        ApartmentServicePrice::whereIn('bdc_vehicle_id', $list)->delete();

        DB::table('bdc_vehicle_cards')
            ->whereIn('bdc_vehicle_id',$list)
            ->update([
                'status'=>0
            ]);

        foreach ($apartments as $apId => $apartment) {
            foreach ($apartment as $cate) {
                $vehicles = Vehicles::where('bdc_apartment_id', $apId)
                    ->where('vehicle_category_id', $cate)
                    ->orderBy('created_at')
                    ->get();

                $vehicle_category = DB::table('bdc_vehicles_category')->where('id', $cate)->first();

                if (empty($vehicle_category)) {
                    continue;
                }

                $progressive = DB::table('bdc_progressives')
                    ->where('name', 'Dịch vụ ' . $vehicle_category->name)
                    ->where('building_id', $vehicle_category->bdc_building_id)
                    ->first();

                if (empty($progressive)) {
                    continue;
                }

                $progressive_prices = DB::table('bdc_progressive_price')
                    ->where('progressive_id',$progressive->id)
                    ->get();

                foreach ($vehicles as $key => $vehicle) {
                    foreach ($progressive_prices as $progressive_price) {
                        if (($key+1 >= $progressive_price->from && $key+1 <= $progressive_price->to) || $progressive_price->from == 0) {
                            ApartmentServicePrice::where('bdc_vehicle_id',$vehicle->id)
                                ->update([
                                    'price'=>$progressive_price->price
                                ]);
                            Vehicles::where('id',$vehicle->id)
                                ->update([
                                    'price'=>$progressive_price->price,
                                    'priority_level'=>$progressive_price->priority_level,
                                    'bdc_progressive_price_id'=>$progressive_price->id
                                ]);
                        }
                    }
                }
            }
        }

        $message = [
            'error'  => 0,
            'status' => 'success',
            'msg'    => "Đã xóa" .count($list)." bản ghi!",
        ];
//
        return response()->json($message);
    }
    public function status($request)
    {
        $ids    = $request->input('ids', []);
        $status = $request->input('status', 1);

        // convert to array
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $list = [];
        foreach ($ids as $id) {
            $list[] = (int) $id;
        }

        $this->model->whereIn('id', (array) $list)->update(['status' => (int) $status]);

        $message = [
            'error'  => 0,
            'status' => 'success',
            'msg'    => 'Đã cập nhật trạng thái!',
        ];

        return response()->json($message);
    }
    public function per_page($request)
    {
        $per_page = $request->input('per_page', 20);

        Cookie::queue('per_page', $per_page, 60 * 24 * 30);

        return back();
    }
    public function action($request)
    {
        $method = $request->input('method', '');
        if ($method == 'delete') {
            $this->deleteAt($request);
        } elseif ($method == 'status') {
            $this->status($request);
        } elseif ($method == 'per_page') {
            $this->per_page($request);
        }
        return back();
    }


    public function getVehicleApartment($id)
    {
        $vehicles = $this->model->where('bdc_apartment_id',$id)->get();
        $vehicle = $vehicles->pluck('name','id')->toArray();
        return $vehicle;
    }

    public function vehicle_apartment_all_status($bdc_apartment_id, $vehicle_category_id)
    {
        return $this->model->where(['bdc_apartment_id'=> $bdc_apartment_id ,'vehicle_category_id'=> $vehicle_category_id,'status'=>1])->orderBy('created_at')->get();
    }

    public function countVehicleByApartmentAndCate($apartment_id, $cate_id)
    {
        return $this->model
            ->where('bdc_apartment_id', $apartment_id)
            ->where('vehicle_category_id', $cate_id)
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->count();
    }
    public function change_status($id)
    {
        $status = 0;

        $vehicle_current = $this->model->find($id);

        $vehicle_current->status = $status;
        if ($status == 0) {
            $vehicle_current->price = null;
            $vehicle_current->priority_level = null;
        } 

        $vehicle_current->save();

        $get_count_vehicle_apartment_all_status = self::vehicle_apartment_all_status($vehicle_current->bdc_apartment_id, $vehicle_current->vehicle_category_id);

        $progressive_prices = @$vehicle_current->bdcVehiclesCategory->progressive->progressivePrice;



        foreach ($get_count_vehicle_apartment_all_status as $key => $vehicle) {
            // kiểm tra xem căn hộ đã gắn vào dịch vụ chưa
            $apartment_vehicle = $vehicle->apartmentServicePrices_v2;

            if ($apartment_vehicle) {
                foreach ($progressive_prices as $progressive_price) {

                    if ($vehicle->status == 1 && ($key + 1 >= $progressive_price->from && $key + 1 <= $progressive_price->to) || $progressive_price->from == 0) {
                        ApartmentServicePrice::where('bdc_vehicle_id', $vehicle->id)
                            ->update([
                                'price' => $progressive_price->price
                            ]);
                        Vehicles::find($vehicle->id)
                            ->update([
                                'price' => $progressive_price->price,
                                'priority_level' => $progressive_price->priority_level,
                                'bdc_progressive_price_id' => $progressive_price->id
                            ]);
                    }
                }
            }
        }

        if($progressive_prices->count() == 1 && $status ==1){
            $vehicle_current->price = $progressive_prices[0]->price;
            $vehicle_current->priority_level = $progressive_prices[0]->priority_level;
        }
        
        $vehicle_current->save();

        DB::table('bdc_vehicle_cards')
            ->where('bdc_vehicle_id', $id)
            ->update([
                'status' => $status
            ]);
        ApartmentServicePrice::where('bdc_vehicle_id', $id)
            ->update([
                'status' => $status
            ]);
    }

}

