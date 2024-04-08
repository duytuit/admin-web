<?php

namespace App\Repositories\Apartments;

//use App\Repositories\Contracts\RepositoryInterface;
use App\Models\Building\BuildingPlace;
use App\Repositories\Customers\CustomersRespository;
use App\Repositories\Eloquent\Repository;
use Illuminate\Support\Facades\Cache;
use const App\Repositories\Service\BUILDING_USER;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\PublicUser\Users;
use App\Models\Apartments\Apartments;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApartmentsRespository extends Repository{


    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\Apartments\Apartments::class;
    }

    public function searchBy($buiding_id,$request,$where=[],$perpage = 20)
    {


        if (!empty($request->floor)) {
            $where[] = ['floor', '=', $request->floor];
        }
        if (!empty($request->place)) {
            $where[] = ['building_place_id', '=', $request->place];
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

        $model = $this->model;

        if ($options['where']) {
            $model = $model->where($options['where']);
        }
        $model = $model->where('building_id',$buiding_id);
        if (!empty($request->name)) {
            $model->Where(function ($query) use ($request) {
                $query->orWhere('name', 'like', '%' . $request->name . '%');
                $query->orWhere('code', 'like', '%' . $request->name . '%');
            });
        }
        if (!empty($request->re_name)) {
            $model->Wherehas('bdcCustomers',function ($query) use ($request) {
                $query->Where('type',0);
                $query->Where('pub_user_profile_id',$request->re_name);
            });
        }
        $list_search = $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
        return $list_search;
    }
    public function searchByOption(array $options = [])
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

    public function searchByApartmentInGroup(array $options = [],$building_id,$apartment_group_id)
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
        $model = $model->where('building_id',$building_id);

        if (isset($apartment_group_id)) {
            $model = $model->orWhere('bdc_apartment_group_id', $apartment_group_id);
        }

        return $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
    }

    public function searchByRelationship(array $options = [])
    {
        $default = [
            'select'   => '*',
            'where'    => [],
            'relationship'    => [],
            'order_by' => 'id DESC',
            'per_page' => 20,
            'keyword' => '',
            'id' => 0,
        ];

        $options = array_merge($default, $options);

        extract($options);

        $model = $this->model->select($options['select']);

        if ($options['where']) {
            $model = $model->where($options['where']);
        }

        if ($options['relationship']) {
            foreach ($options['relationship'] as $key => $table){
                if($table == 'bdc_customers'){
                    $model->whereHas('bdc_customers', function ($query) use ($options) {
                        if($options['id'] != 0){
                            $query->where('id', '=', $options['id']);
                        }
                        if($options['keyword'] != ''){
                            $query->where('name', 'like', '%' . $options['keyword'] . '%');
                        }
                    });
                }
            }
        }
//
//        dd($model->orderByRaw($options['order_by'])->paginate($options['per_page']));
        return $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
    }

    public function findById($id)
    {
        return $this->model->where('id', $id)->first();
    }

    public function findById_v2($building_id, $id)
    {
        return $this->model->where(['building_id' => $building_id, 'id' => $id])->first();
    }

    public function findByIdBuildingId($building_id, $id)
    {
        return $this->model->where(['building_id' => $building_id, 'id' => $id])->get();
    }

    public function findByCode($building_id,$code)
    {
        return $this->model->where(['building_id' => $building_id, 'code' => (string)$code])->first();
    }

    public function findByGroup($building_id,$groupId)
    {
        return $this->model->where('building_id',$building_id)->where('bdc_apartment_group_id',$groupId)->pluck('id')->toArray();
    }

    public function findByPlaceId($building_id,$planceId)
    {
        return $this->model->where('building_id',$building_id)->where('building_place_id',$planceId)->pluck('id');
    }

    public function findByCodeCustomer($building_id,$code_customer)
    {
        return $this->model->where(['building_id' => $building_id, 'code_customer' => $code_customer])->first();
    }

    public function checkExitByCode($building_id, $code, $apartmentId = null)
    {
        return $this->model->where(['building_id' => $building_id])->where(function ($query) use ($code) {
            if ($code) {
                $query->where('code_customer', $code)
                ->orWhere('code_water', $code)
                ->orWhere('code_electric', $code);
            }
        })
        ->where(function ($query) use ($apartmentId) {
            if ($apartmentId) {
                $query->where('id', '<>', $apartmentId);
            }
        })->first();
    }

    public function findByCodeCustomerWithUpdateApartment($building_id,$code_customer,$ApartmentId)
    {
        $list[] = (int)$ApartmentId;
        return $this->model->whereNotIn('id',$list)->where(['building_id' => $building_id, 'code_customer' => $code_customer])->first();
    }

    public function findCheckDuplicateByCode($building_id, $code, $apartmentId)
    {
        return $this->model->where(['building_id' => $building_id, 'code' => $code])->where('id', '<>' , $apartmentId)->first();
    }

    public function findByName($name)
    {
        return $this->model->where(['name'=>$name])->first();
    }
    public function findByNamev2($name,$building_id,$building_place_id = null)
    {
        $model = $this->model->where(['name'=>$name,'building_id' =>$building_id])->get();
        if($building_place_id){
            return $model->where('building_place_id',$building_place_id)->first();
        }
        return $model->first();
    }

    public function findByName_v3($building_id, $building_place_id, $name)
    {
        return $this->model->where(['building_id' =>$building_id,'name'=>$name,'building_place_id'=>$building_place_id])->first();
    }
    
    public function find_check_duplicate_ByName_v3($building_id, $building_place_id, $name, $apartmentId)
    {
        return $this->model->where(['building_id' =>$building_id,'name'=>$name,'building_place_id'=>$building_place_id])->where('id', '<>' , $apartmentId)->first();
    }
    public function findByNameFloorPlace($name,$floor,$place_id,$building_id)
    {
        return $this->model->where('name',$name)->where('floor',(int)$floor)->where('building_place_id',$place_id)->where('building_id',(int)$building_id)->first();
    }

    public function findByBuildingIdPage($request,$buildingId,$per_page)
    {
        return $this->model->where('building_id', $buildingId)->where(function($query) use($request){
            if(isset($request->name) && $request->name !=null){
                $query->where('name','like','%'.$request->name.'%');
            }
        })->paginate($per_page);
    }
    public function findByBuildingId ($buildingId)
    {
        return $this->model->where('building_id', $buildingId)->get();
    }

    public function searchByAll(array $options = [],$building_id, $building_place_id = null)
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
        $model = $model->where('building_id',$building_id);
        if($building_place_id){
          $model = $model->where('building_place_id',$building_place_id);
        }
       
        return $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
    }
    public function searchByAll_v2(array $options = [],$building_id,$per_page = 20, $building_place_id = null)
    {
       
        $default = [
            'select'   => ['id','name'],
            'where'    => [],
            'order_by' => 'id DESC',
            'per_page' => $per_page,
        ];

        $options = array_merge($default, $options);
        extract($options);

        $model = $this->model->select($options['select']);
        if ($options['where']) {
            $model = $model->where($options['where']);
        }
        $model = $model->where('building_id',$building_id);
        if($building_place_id){
          $model = $model->where('building_place_id',$building_place_id);
        }
       
        return $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
    }

    public function apartmentAllArray($building_id)
    {
        return array_map(function($item){ return $item['name']; }, $this->model->select('name')->where('building_id',$building_id)->get()->toArray());
    }
    public function getbyIds($ids)
    {
        return $this->model->whereIn('id',$ids)->get();
    }
    public function getbyIdsV2($ids)
    {
        return $this->model->whereIn('id',$ids)->orderBy('building_id','asc')->get();
    }
    public function insert(array $data) {
        return $this->model->insert($data);
    }

    public function getDataFile($file,$building_id)
    {
        $path = $file->getRealPath();

        $excel_data = Excel::load($path)->get();

        storage_path('upload', $file->getClientOriginalName());

        $url = [
            'name' => $file->getClientOriginalName(),
            'uuid' => (string) \Webpatser\Uuid\Uuid::generate(),
        ];

        if ($excel_data->count()) {
            $excel_apartment = $this->unsetApartment($excel_data);
            $data_apartment = $this->apartmentExelData($excel_apartment,$url,$building_id);
            $save = $this->apartmentDataSave($data_apartment,$building_id);
        }

        return $save;
    }

    public function unsetApartment($apartments)
    {
        $duplicate = [];
        for ($i = 0; $i < count($apartments) - 1; $i++) {
            for ($j = $i + 1; $j < count($apartments); $j++) {
                if (!empty($apartments[$j]) && $apartments[$i]['name'] !== null && $apartments[$i]['name'] == $apartments[$j]['name']) {
                    $duplicate[$j] = $apartments[$j];
                    unset($apartments[$j]);
                }elseif (!empty($apartments[$j]) && $apartments[$i]['code'] !== null && $apartments[$i]['code'] == $apartments[$j]['code']) {
                    $duplicate[$j] = $apartments[$j];
                    unset($apartments[$j]);
                }
            }
        }
        $aptm = [];
        for ($i = 0; $i < count($apartments); $i++) {
            if (isset($apartments[$i])) {
                if ($apartments[$i]['index'] == null &&
                    $apartments[$i]['name'] == null &&
                    $apartments[$i]['floor'] == null &&
                    $apartments[$i]['area'] == null &&
                    $apartments[$i]['description'] == null &&
                    $apartments[$i]['status'] == null &&
                    $apartments[$i]['place'] == null &&
                    $apartments[$i]['code'] == null) {

                    continue;
                }
                $aptm[]= $apartments[$i];
            }

        }
        $data = [
            'data' => $aptm,
            'duplicate' => $duplicate,
        ];
        return $data;
    }

    public function apartmentExelData($apartments,$url,$building_id)
    {
        $check_apartment = $this->apartmentAllArray($building_id);
        $has_ap=[];$fail_ap=[];$new_ap=[];$miss_ap=[];$messages=[];
        foreach ($apartments['data'] as $key => $ap) {
            if (!in_array($ap->name, $check_apartment)){
                if($ap->index && $ap->name && $ap->floor && $ap->place){
                    $place = BuildingPlace::where('bdc_building_id',$building_id)->where('code',$ap->place)->first();
                    if($place){
                        $new_ap[] = [
                            'index'=> $ap->index,
                            'name' => $ap->name,
                            'floor' => $ap->floor,
                            'area' => $ap->area,
                            'description' => $ap->description,
                            'status' => $ap->status,
                            'building_place_id' => $ap->place,
                            'code' => $ap->code??null,
                        ];
                    }else{
                        $miss_ap[] = [
                            'index'=> $ap->index,
                            'name' => $ap->name,
                            'floor' => $ap->floor,
                            'area' => $ap->area,
                            'description' => $ap->description,
                            'status' => $ap->status,
                            'building_place_id' => $ap->place,
                            'code' => $ap->code??null,
                        ];
                    }
                }else{
                    $fail_ap[] = [
                        'index'=> $ap->index,
                        'name' => $ap->name,
                        'floor' => $ap->floor,
                        'area' => $ap->area,
                        'description' => $ap->description,
                        'status' => $ap->status,
                        'building_place_id' => $ap->place,
                        'code' => $ap->code??null,
                    ];
                }
            } else {
                $has_ap[] = [
                    'index'=> $ap->index,
                    'name' => $ap->name,
                    'floor' => $ap->floor,
                    'area' => $ap->area,
                    'description' => $ap->description,
                    'status' => $ap->status,
                    'building_place_id' => $ap->place,
                    'code' => $ap->code??null,
                ];
            }
        }

        if (!empty($has_ap)) {
            $messages[]= [
                'messages' => 'Có ' . count($has_ap) . ' căn hộ đã có trên hệ thống',
                'data'     => $has_ap,
            ];
        }

        if(!empty($apartments['duplicate'])){
            $dlc[]= json_decode(reset($apartments['duplicate']), True);
            $messages[]= [
                'messages' => 'Có ' . count($apartments['duplicate']) . ' căn hộ bị trùng trong file',
                'data'     => $dlc,
            ];
        }
        if(!empty($new_ap)){
            $messages[]= [
                'messages' => 'Có ' . count($new_ap) . ' căn hộ đầy đủ dữ liệu',
                'data'     => $new_ap,
            ];
        }
        if(!empty($fail_ap)){
            $messages[]= [
                'messages' => 'Có ' . count($fail_ap) . ' căn hộ bị thiếu dữ liệu',
                'data'     => $fail_ap
            ];
        }
        if(!empty($miss_ap)){
            $messages[]= [
                'messages' => 'Có ' . count($miss_ap) . ' căn hộ bị sai mã tòa',
                'data'     => $miss_ap
            ];
        }
        $data['messages'] = $messages;

        $data_new = [
            'data_apt' =>$new_ap,
            'url_file'  => $url,
            'duplicate' => $apartments['duplicate']??[],
        ];
        $data['data'] = $data_new;

        return $data;
    }
    public function apartmentDataSave($dataExel,$building_id)
    {
        $time = Carbon::now();
        $data_ap=[];
        if ($dataExel['data']['data_apt'] && !(session()->get('errors_user'))) {
            foreach ($dataExel['data']['data_apt'] as $index => $apartment) {
                if($apartment['building_place_id']){
                    $place = BuildingPlace::where('bdc_building_id',$building_id)->where('code',$apartment['building_place_id'])->first();
                    if($apartment['code']){
                        $code = $apartment['code'];
                    }else{
                        $code = $apartment['building_place_id'].'-'.$apartment['floor'].'-'.$apartment['name'];
                    }
                }

                $data_ap[] = [
                    'building_id' => $building_id??0,
                    'index'  => $apartment['index'],
                    'name'  => $apartment['name'],
                    'description' => $apartment['description'],
                    'floor' => $apartment['floor'],
                    'area' => $apartment['area'],
                    'status' => $apartment['status'],
                    'building_place_id' => $place?$place->id:null,
                    'code' => $code?$code:null,
                    'created_at'     => $time,
                    'updated_at'     => $time,
                ];
            }

        }
        $dataExel['data'] = array_merge($dataExel['data'],['apartments' =>$data_ap]);
        return $dataExel;
    }
    public function countItem($building = 0)
    {
       return $this->model->where('building_id',$building)->count();
    }

    public function getApartmentOfBuilding($building)
    {
        return $this->model->where('building_id',$building)->get();
    }

    public function getApartmentOfBuildingV2($building)
    {
        return $this->model->where('building_id',$building);
    }

    public function getApartmentOfBuildingDebit($id)
    {
        $result = $this->model->where('building_id', $id)->pluck('name','id');
        return $result ?  $result->toArray() : null;
    }
    public function getIdApartmentOfBuilding($id)
    {
        $result = $this->model->where('building_id', $id)->pluck('id');
        return $result ?  $result->toArray() : null;
    }

    public function getApartmentOfBuildingV3($building_id)
    {
        return $this->model->where('building_id', $building_id)->select('id','name')->orderBy('created_at', 'desc')->get();
    }

    public function getApartmentById($id)
    {
        $result = $this->model->select('name', 'id')->whereIn('id',$id)->pluck('name','id');
        return $result ?  $result->toArray() : null;
    }
    public function getApartmentFloor()
    {
        $result = $this->model->select('floor')->distinct()->orderBy('floor', 'asc')->get();
        return $result ?  $result->toArray() : null;
    }
    public function restoreSelects($request)
    {
        $this->model->withTrashed()->whereIn('id',$request->ids)->restore();
    }
    public function deleteSelects($request)
    {
        if(Auth::user()->isadmin == 1){
            $this->model->whereIn('id',$request->ids)->update(['deleted_by'=>auth()->user()->id]);
            return $this->model->whereIn('id',$request->ids)->delete();
        }else{
            $this->model->whereIn('id',$request->ids)->doesnthave('debit_v2')->update(['deleted_by'=>auth()->user()->id]);
            return $this->model->whereIn('id',$request->ids)->doesnthave('debit_v2')->delete();
        }
       
    }
    public function getAllId($request)
    {
        return $this->model->whereIn('id',$request->ids)->delete();
    }
    public function getAllByFloor($floors,$building_id)
    {
        $result = $this->model->whereIn('floor',$floors)->where('building_id',$building_id)->select('id')->distinct()->get();
        return $result ?  $result->toArray() : null;
    }
    public function getOneApartmentBuilding($id,$building_id)
    {
        $result = $this->model->where('id',$id)->where('building_id',$building_id)->first();
        return $result ?  $result->toArray() : null;
    }
    public function updateStatus($id,$building_id,array $data)
    {
        return $this->model->where('id',$id)->where('building_id',$building_id)->update($data);
    }
    public function getDataExport($building_id, $request,$where=[])
    {
        if (!empty($request->floor)) {
            $where[] = ['floor', '=', $request->floor];
        }
        if (!empty($request->place)) {
            $where[] = ['building_place_id', '=', $request->place];
        }

        if ($request->status != null && $request->status != 'false') {
            $where[] = ['status', '=', $request->status];
        }

        $default = [
            'select'   => '*',
            'where'    => $where,
            'order_by' => 'id DESC'
        ];

        $options = array_merge($default, $where);

        extract($options);

        $model = $this->model->select($options['select']);

        if ($options['where']) {
            $model = $model->where($options['where']);
        }
        $model = $model->where('building_id',$building_id);
        if (!empty($request->name)) {
            $model->Where(function ($query) use ($request) {
                $query->orWhere('name', 'like', '%' . $request->name . '%');
                $query->orWhere('code', 'like', '%' . $request->name . '%');
            });
        }
        if (!empty($request->re_name)) {
            $model->Wherehas('bdcCustomers',function ($query) use ($request) {
                $query->Where('type',0);
                $query->Where('pub_user_profile_id',$request->re_name);
            });
        }
        $list_search = $model->orderByRaw($options['order_by'])->get();
        return $list_search;
    }

    public function unsetApartmentSyncs($apartments)
    {
        $duplicate = [];
        for ($i = 0; $i < count($apartments) - 1; $i++) {
            for ($j = $i + 1; $j < count($apartments); $j++) {
                if (!empty($apartments[$j]) && $apartments[$i]['canho'] !== null && $apartments[$i]['canho'] == $apartments[$j]['canho']) {
                    $duplicate[$j] = $apartments[$j];
                    unset($apartments[$j]);
                }
            }
        }
        for ($i = 0; $i < count($apartments); $i++) {
            if (isset($apartments[$i])) {
                if ($apartments[$i]['index'] == null &&
                    $apartments[$i]['canho'] == null &&
                    $apartments[$i]['macan'] == null) {

                    unset($apartments[$i]);
                }
            }

        }
        $data = [
            'data' => $apartments,
            'duplicate' => $duplicate,
        ];
        return $data;
    }
    public function checkApartment($id,$building_id)
    {
        return $this->model->where('id', $id)->where('building_id',$building_id)->first();
    }

    public function getFloorCustomerIds($id, $active_building)
    {
        // return $this->model->with('bdcCustomers.pubUserProfile')->where('id',815)->get();
        return $this->model
        ->with(['bdcCustomers.pubUserProfile'=> function($query) use ($id, $active_building){

            return $query->where('pub_user_id', $id)->where('status', self::STATUS_ACTIVE)->where('type',Users::USER_APP)->where('bdc_building_id', $active_building);
        }])
        ->whereHas('bdcCustomers.pubUserProfile', function($query) use ($id, $active_building){

            return $query->where('pub_user_id', $id)->where('status', self::STATUS_ACTIVE)->where('type',Users::USER_APP)->where('bdc_building_id', $active_building);
        })
        ->get();
    }

    public static function getInfoApartmentsById($id)
    {
        $keyCache = "getInfoApartmentsById_" . $id;
        return Cache::remember($keyCache, 10 ,function () use ($keyCache, $id) {
            $rs = Apartments::withTrashed()->where([
                "id" => $id
            ])->first();
            if (!$rs) return null;
            return (object) $rs->toArray();
        });
    }
}
