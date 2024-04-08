<?php

namespace App\Repositories\PublicUsers;

//use App\Repositories\Contracts\RepositoryInterface;
use App\Models\Apartments\Apartments;
use App\Models\PublicUser\UserInfo;
use App\Models\PublicUser\Users;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\Eloquent\Repository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Customers\Customers;
use App\Models\PublicUser\V2\User;
use App\Models\PublicUser\V2\UserInfo as V2UserInfo;

class PublicUsersProfileRespository extends Repository {



    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\PublicUser\UserInfo::class;
    }
    public function findAllBy($colums = 'id',$id)
    {
        return $this->model->where($colums, $id)->get();
    }
    public function findAllBy_v1($id)
    {
        return $this->model->find($id);
    }
    public function findAllByOne($colums = 'id',$id)
    {
        return $this->model->where($colums, $id)->first();
    }

    public function getOne($colums = 'id',$id)
    {
        return $this->model->where($colums, $id)->with(['bdcCustomers','bdcCustomers.bdcApartment'])->first();
    }
    public function searchBy(array $options = [])
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

    public function searchByCustomer($buiding_id, $request, $where = [], $whereIn = [], $perpage = 20)
    {

        $default = [
            'select'   => '*',
            'where'    => $where,
            'order_by' => 'id DESC',
            'per_page' => $perpage,
        ];

        $options = array_merge($default, $where);
        extract($options);
        $model = $this->model->select($options['select']);
        if($whereIn){
            $model = $model->whereIn('id',$whereIn);
        }
        $model = $model->where('bdc_building_id',$buiding_id);
        if(!empty($request->apartment)){
            $where[]= $model->whereHas('bdcCustomers.bdcApartment', function ($query) use ($request) {
                $query->where('id', '=', $request->apartment);
            });
        }
        if ($request->type != '0' && gettype($request->type)!=="NULL") {
            $where[]= $model->whereHas('bdcCustomers', function ($query) use ($request) {
                $query->where('type', $request->type);
            });
        }
        if(!empty($request->place)){
            $where[]= $model->whereHas('bdcCustomers.bdcApartment', function ($query) use ($request) {
                $query->where('building_place_id', '=', $request->place);
            });
        }
        if(!empty($request->keyword)){
            $model = $model->where('display_name','like','%'.$request->keyword.'%');
        }
        if(!empty($request->email)){
            $model = $model->where('email','like','%'.$request->email.'%');
        }
        if(!empty($request->phone)){
            $model = $model->where('phone','like','%'.$request->phone.'%');
        }
        if(!empty($request->birthday)){
            $model = $model->where('birthday', date('Y-m-d',strtotime($request->birthday)));
        }
        if(!empty($request->birthday_day) && !empty($request->birthday_month) && !empty($request->birthday_from_year) && !empty($request->birthday_to_year)){
            $model = $model->whereRaw('DAY(birthday) ='.(int)$request->birthday_day);
            $model = $model->whereRaw('MONTH(birthday) ='.(int)$request->birthday_month);
            $model = $model->whereYear('birthday', '>=', (int)$request->birthday_from_year);
            $model = $model->whereYear('birthday', '<=', (int)$request->birthday_to_year);
        }
        if(!empty($request->gender)){
            $model = $model->where('gender','=',$request->gender);
        }
        
        $list_search = $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
        $list_search->load('bdcCustomers','bdcCustomers.bdcApartment', 'pubusers');

        return $list_search;
    }
    public function searchByCustomerNew($buiding_id, $request, $where = [], $whereIn = [], $perpage = 20)
    {

        $default = [
            'select'   => '*',
            'where'    => $where,
            'order_by' => 'id DESC',
            'per_page' => $perpage,
        ];

        $options = array_merge($default, $where);
        extract($options);
        $model = $this->model->select($options['select']);
        if($whereIn){
            $model = $model->whereIn('id',$whereIn);
        }
        $model = $model->where('bdc_building_id',$buiding_id);
        if(!empty($request->apartment)){
            $where[]= $model->whereHas('bdcCustomers.bdcApartment', function ($query) use ($request) {
                $query->where('id', '=', $request->apartment);
            });
        }
        if(!empty($request->place)){
            $where[]= $model->whereHas('bdcCustomers.bdcApartment', function ($query) use ($request) {
                $query->where('building_place_id', '=', $request->place);
            });
        }
        if(!empty($request->keyword)){
            $model = $model->where('display_name','like','%'.$request->keyword.'%');
        }
        if(!empty($request->email)){
            $model = $model->where('email','like','%'.$request->email.'%');
        }
        if(!empty($request->phone)){
            $model = $model->where('phone','like','%'.$request->phone.'%');
        }
        if(!empty($request->birthday)){
            $model = $model->where('birthday', date('Y-m-d',strtotime($request->birthday)));
        }
        if(!empty($request->gender)){
            $model = $model->where('gender','=',$request->gender);
        }

        $list_search = $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
        $list_search->load('bdcCustomers','bdcCustomers.bdcApartment', 'pubusers');

        return $list_search;
    }

    public function searchByRelationship($key,$building_id)
    {

        $options = [
            'select'   => ['id','display_name'],
            'where'    => [],
            'order_by' => 'id DESC',
            'per_page' => 20,
        ];
        $model = $this->model->select($options['select']);
        if($key){
            $model= $model->where('display_name','like','%'.$key.'%');
        }
        $model= $model->where('bdc_building_id',$building_id);
        return $model->orderByRaw($options['order_by'])->paginate($options['per_page']);

    }

    public function searchByRelationshipV2($key,$building_id)
    {
        
        $options = [
            'select'   => ['id','full_name'],
            'where'    => [],
            'order_by' => 'id DESC',
            'per_page' => 20,
        ];
        $model = V2UserInfo::select($options['select']);
        if($key){
            $model= $model->where('full_name','like','%'.$key.'%');
        }
        $model= $model->whereHas('apartment',function($query)use($building_id){
                $query->where('building_id',$building_id)->where('type',0);
        });
        return $model->orderByRaw($options['order_by'])->paginate($options['per_page']);

    }

    public function searchByweb($key)
    {
        $options = [
            'select'   => '*',
            'where'    => [],
            'order_by' => 'id DESC',
            'per_page' => 20,
        ];
        $model = $this->model->select($options['select']);
        if($key){
            $model= $model->where('display_name','like','%'.$key.'%');
        }
        $model= $model->where('type','=',2);
        return $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
    }
    public function searchByNomal($key)
    {
        $options = [
            'select'   => '*',
            'where'    => [],
            'order_by' => 'id DESC',
            'per_page' => 20,
        ];
        $model = $this->model->select($options['select']);
        if($key){
            $model= $model->where('display_name','like','%'.$key.'%');
        }
        return $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
    }
    public function checkPhone($request)
    {
        return $this->model->where('phone',$request->phone)->first();
    }

    public function checkProfileVsCustomer($request)
    {
        return $this->model->where('phone',$request->phone)->whereHas('bdcCustomers', function ($query) use ($request) { $query->where('bdc_apartment_id', '=', $request->bdc_apartment_id); })->first();
    }

    public function customerAllByphone($building_id)
    {
        return array_map(function($item){ return $item['phone']; }, $this->model->select('phone')->where('bdc_building_id',$building_id)->where('type',Users::USER_APP)->get()->toArray());
    }
    public function customerAllByemail($building_id)
    {
        return array_map(function($item){ return $item['email']; }, $this->model->select('email')->where('bdc_building_id',$building_id)->where('type',Users::USER_APP)->get()->toArray());
    }
    public function insert(array $data) {
        return $this->model->insert($data);
    }

    public function getDataFile($file,$building_id,$app_id)
    {
        $path = $file->getRealPath();

        $excel_data = Excel::load($path)->get();

        storage_path('upload', $file->getClientOriginalName());

        $url = [
            'name' => $file->getClientOriginalName(),
            'uuid' => (string) \Webpatser\Uuid\Uuid::generate(),
        ];

        if ($excel_data->count()) {
            $excel_customer = $this->unsetCustomer($excel_data);
            $data_customer = $this->customerExelData($excel_customer,$url,$building_id);
            $save = $this->customerDataSave($data_customer,$building_id,$app_id);
        }

        return $save;
    }

    public function unsetCustomer($customers)
    {
        $duplicate=[];
        $cus = [];
        foreach ($customers as $key => $item){
                $cus[]=$item;
        }
        $data = [
            'data' => $cus,
            'duplicate' => $duplicate,
        ];
        return $data;
    }

    public function customerExelData($customers,$url,$building_id)
    {

        $check_phone = $this->customerAllByphone($building_id);
        $check_email = $this->customerAllByemail($building_id);
        $has_ap=[];$fail_ap=[];$new_ap=[];
        foreach ($customers['data'] as $key => $cus) {
            if($cus->index && $cus->name && ($cus->type == 2 || $cus->type == 5)){
                $new_ap[] = [
                    'index'=> $cus->index,
                    'display_name' => $cus->name,
                    'cmt' => $cus->cmt,
                    'phone' => isset($cus->phone) ? str_replace(array('-', '.', ' '), '', $cus->phone) : null,
                    'birthday' => $cus->birthday,
                    'email' => $cus->email,
                    'password' => $cus->password,
                    'gender' => $cus->sex,
                    'type' => $cus->type,
                    'apartment_name' => $cus->apartment_name,
                    'floor' => $cus->floor,
                    'place' => $cus->place,
                ];
            }else{
                if ( !in_array($cus->phone, $check_phone) || !in_array($cus->email, $check_email)) {
                    if($cus->index && $cus->name && ($cus->type != 2 || $cus->type != 5) && ($cus->phone || $cus->email)){
                        $new_ap[] = [
                            'index'=> $cus->index,
                            'display_name' => $cus->name,
                            'cmt' => $cus->cmt,
                            'phone' => isset($cus->phone) ? str_replace(array('-', '.', ' '), '', $cus->phone) : null,
                            'birthday' => $cus->birthday,
                            'email' => $cus->email,
                            'password' => $cus->password,
                            'gender' => $cus->sex,
                            'type' => $cus->type,
                            'apartment_name' => $cus->apartment_name,
                            'floor' => $cus->floor,
                            'place' => $cus->place,
                        ];
                    }else{
                        $fail_ap[] = [
                            'index'=> $cus->index,
                            'display_name' => $cus->name,
                            'cmt' => $cus->cmt,
                            'phone' => isset($cus->phone) ? str_replace(array('-', '.', ' '), '', $cus->phone) : null,
                            'birthday' => $cus->birthday,
                            'email' => $cus->email,
                            'password' => $cus->password,
                            'gender' => $cus->sex,
                            'type' => $cus->type,
                            'apartment_name' => $cus->apartment_name,
                            'floor' => $cus->floor,
                            'place' => $cus->place,
                        ];
                    }
                } else {
                    $has_ap[] = [
                        'index'=> $cus->index,
                        'display_name' => $cus->name,
                        'cmt' => $cus->cmt,
                        'phone' => isset($cus->phone) ? str_replace(array('-', '.', ' '), '', $cus->phone) : null,
                        'birthday' => $cus->birthday,
                        'email' => $cus->email,
                        'password' => $cus->password,
                        'gender' => $cus->sex,
                        'type' => $cus->type,
                        'apartment_name' => $cus->apartment_name,
                        'floor' => $cus->floor,
                        'place' => $cus->place,
                    ];
                }
            }

        }
        if (!empty($has_ap)) {
            $messages[]= [
                'messages' => 'Có ' . count($has_ap) . ' cư dân đã có trên hệ thống',
                'data'     => $has_ap,
            ];
        }

        if(!empty($customers['duplicate'])){
            $dlc[]= json_decode(reset($customers['duplicate']), True);
            $messages[]= [
                'messages' => 'Có ' . count($customers['duplicate']) . ' cư dân bị trùng trong file',
                'data'     => $dlc,
            ];
        }
        if(!empty($new_ap)){
            $messages[]= [
                'messages' => 'Có ' . count($new_ap) . ' cư dân đầy đủ dữ liệu',
                'data'     => $new_ap,
            ];
        }
        if(!empty($fail_ap)){
            $messages[]= [
                'messages' => 'Có ' . count($fail_ap) . ' cư dân bị thiếu dữ liệu',
                'data'     => $fail_ap
            ];
        }
        $data['messages'] = $messages;

        $data_new = [
            'data_cus' =>$new_ap,
            'has_cus' =>$has_ap,
            'url_file'  => $url,
            'duplicate' => $customers['duplicate']??[],
        ];
        $data['data'] = $data_new;
        return $data;
    }

    public function customerDataSave($dataExel,$building_id,$app_id)
    {
        $data_ap=[];
        if ($dataExel['data']['data_cus'] && !(session()->get('errors_user'))) {
            foreach ($dataExel['data']['data_cus'] as $index => $cus) {
                $data_ap[] = [
                    'display_name'  => $cus['display_name'],
                    'cmt' => $cus['cmt'],
                    'phone' => isset($cus['phone']) ? str_replace(array('-', '.', ' '), '', $cus['phone']) : null,
                    'birthday' => date('Y-m-d', strtotime($cus['birthday'])),
                    'email' => $cus['email'],
                    'gender' => $cus['gender'],
                    'type' => Users::USER_APP,
                    'bdc_building_id' => $building_id,
                    'app_id' => $app_id??'buildingcare',
                ];
            }

        }
        if ($dataExel['data']['has_cus'] && !(session()->get('errors_user'))) {
            foreach ($dataExel['data']['has_cus'] as $index => $cus) {
                $data_ap[] = [
                    'display_name'  => $cus['display_name'],
                    'cmt' => $cus['cmt'],
                    'phone' => isset($cus['phone']) ? str_replace(array('-', '.', ' '), '', $cus['phone']) : null,
                    'birthday' => date('Y-m-d', strtotime($cus['birthday'])),
                    'email' => $cus['email'],
                    'gender' => $cus['gender'],
                    'type' => Users::USER_APP,
                    'bdc_building_id' => $building_id,
                    'app_id' => $app_id??'buildingcare',
                ];
            }

        }
        $dataExel['data'] = array_merge($dataExel['data'],['customers' =>$data_ap]);
        return $dataExel;
    }

    public function insertProfile($data,$apt_id,$type)
    {
        $time = Carbon::now();
        $insert_profile = $this->model->create($data);
        $data_cus= ['bdc_apartment_id'=>$apt_id,'pub_user_profile_id'=>$insert_profile->id??0,'type'=>$type,'created_at'=> $time,'updated_at'=> $time];
        return $data_cus;
    }
    public function insertProfileNew($data,$apt_id,$type)
    {
        $data['type']=1;
        $time = Carbon::now();
        $insert_profile = $this->model->create($data);
        $data_cus= ['bdc_apartment_id'=>$apt_id,'pub_user_profile_id'=>$insert_profile->id??0,'type'=>$type,'created_at'=> $time,'updated_at'=> $time,'status_confirm'=> $data['status_confirm'],'handover_date'=> $data['handover_date'],'note_confirm'=> $data['note_confirm'],'is_resident'=> 1];
        return $data_cus;
    }
    public function checkUsersExit($email,$phone,$buiding_id, $type = Null){
        if($email || $phone){
            $check = $this->model->select('*');
            $check = $check->whereHas('pubusers', function ($query) use ($email,$phone) {
                if($email != ''){
                    $query->where('email', '=', $email);
                }
                if($phone != ''){
                    $query->where('mobile', '=', $phone);
                }
            });
            $check = $check->where('bdc_building_id',$buiding_id);
            if($type){
                $check = $check->where('type',$type);
            }
            $check = $check->first();
            return $check;
        }
        return false;
    }
    public function findByPubUserId($ids)
    {
      return $this->model->whereIn('pub_user_id', $ids)->get();
    }
    public function findByidsSelectEmail($ids)
    {
      return $this->model->select('email')->whereIn('id', $ids)->whereRaw('email IS NOT NULL')->distinct()->get()->toArray();
    }
    public function findByidsSelectSms($ids)
    {
      return $this->model->select('phone')->whereIn('id', $ids)->whereRaw('phone IS NOT NULL')->distinct()->get()->toArray();
    }

    public function getStaffActive($staff, $buildingId)
    {
        return $this->model->whereNotIn('pub_user_id', $staff->pluck('pub_user_id')->toArray())->where('status', $this->model::STATUS_ACTIVE)->where('type', Users::USER_WEB)
                            // ->whereHas('bdcDepartmentStaff.department', function($query) use ($buildingId) {
                            //     if (isset($buildingId)) {
                            //         $query->where('bdc_building_id', '=', $buildingId);
                            //     }
                            // })
                            ->where('bdc_building_id', $buildingId)->get();
    }
    public function getStaffActive1($staff, $buildingId)
    {
        return $this->model->whereNotIn('pub_user_id', $staff->pluck('pub_user_id')->toArray())->where('status', $this->model::STATUS_ACTIVE)->where('type', Users::USER_WEB)
                            ->where('bdc_building_id', $buildingId)->where('data_type','V2')->get();
    }

    public function findByBuildingId($id)
    {
        return $this->model->where('bdc_building_id', $id)->get();
    }

    public function findByPubUserIdAndBuildingId($ids, $building_id)
    {
      return $this->model->whereIn('pub_user_id', $ids)->where('type', Users::USER_WEB)->where('bdc_building_id', $building_id)->get();
    }
    public function findByPubUserIdResident($id, $building_id)
    {
      return $this->model->where('pub_user_id', $id)->where('bdc_building_id', $building_id)->where('type',Users::USER_APP)->first();
    }
    public function findByPubUserIdByDeadApartment($building_id,$bdc_apartment_id)
    {
      return $this->model->where(['bdc_building_id' => $building_id,'type' => Users::USER_APP,'status'=>1])->whereHas('bdcCustomers' , function($query) use ($bdc_apartment_id){
              $query->whereNull('is_resident')
                    ->where(['bdc_apartment_id'=> $bdc_apartment_id, 'type' => 0]); //chủ hộ
      })->first();
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
            $list[] = (int) $id;
        }

        $number = $this->model->destroy($list);

        $message = [
            'error'  => 0,
            'status' => 'success',
            'msg'    => "Đã xóa $number bản ghi!",
        ];

        return $message;
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

        return $message;
    }
    public function per_page($request)
    {
        $per_page = $request->input('per_page',20);

        Cookie::queue('per_page', $per_page, 60 * 24 * 30);

        return back();
    }
    public function action($request,$building_id)
    {
        $method = $request->input('method', '');
        if ($method == 'delete') {
            Customers::whereIn('id', $request->ids)->delete();
            return back()->with('success','xóa thành công');
        } elseif ($method == 'status') {
            $status =  $this->status($request);
            return back()->with('success',$status['msg']);
        } elseif ($method == 'per_page') {
            return $this->per_page($request);
        }
        return back();
    }
    public function action_customer_ids($request)
    {
        $method = $request->input('method', '');
        if ($method == 'delete') {
            
            //chỉnh sửa bởi duytuit ngày 07/07/2020
            $get_cus = Customers::whereIn('id', $request->ids)->get();
            foreach ($get_cus as $item) {
                $check_has_profile = $this->model->find($item->pub_user_profile_id);
                if($check_has_profile){
                    $this->model->find($item->pub_user_profile_id)->delete();
                }
                $item->delete();
            }
            return back()->with('success','xóa thành công!');
        } elseif ($method == 'status') {
            $status =  $this->status($request);
            return back()->with('success',$status['msg']);
        } elseif ($method == 'per_page') {
            return $this->per_page($request);
        }
        return back();
    }

    public function checkChangeProfile($pub_user,$builing_id,$app_id,$type)
    {
        return $this->model->where("pub_user_id",$pub_user)->where('bdc_building_id',$builing_id)->where('app_id',$app_id)->where('type',$type)->where('type_profile',0)->first();
    }

    public function checkPhoneParam($phone)
    {
        return $this->model->where('phone',$phone)->first();
    }
    public function getDataExport(array $select,$building_id)
    {
        return $this->model->select($select)->where('bdc_building_id',$building_id)->whereHas('bdcCustomers', function ($query) {
            $query->whereNull('is_resident');
        })->where('type',Users::USER_APP)->get();
    }
    public function getInfoByPubuserId($pubuserid,$building_id)
    {
        return $this->model->where('pub_user_id',$pubuserid)->where('bdc_building_id',$building_id)->where('type',Users::USER_WEB)->first();
    }

    public function getInfoByPubuserByBuildingId($ids)
    {
        return Users::whereIn('id',$ids)->select('id','email')->get();
    }

    public function getInfoById($building_id,$id)
    {
        return $this->model->where('bdc_building_id',$building_id)->where('id',$id)->where('type',Users::USER_APP)->first();
    }

    public function getInfoByPubuserIdV2($pubuserid)
    {
        return $this->model->where('pub_user_id',$pubuserid)->where('type',Users::USER_WEB)->first();
    }

    public function getByPubUserId($id)
    {
        return array_map(function($item){ return $item['id']; }, $this->model->select('id')->where('pub_user_id', $id)->where('type',1)->get()->toArray());
    }
    public function getLastIdWithPrefix($pub_user_id, $customer_code_prefix=null, $company = null)
    {
        if (!$company || !$customer_code_prefix) {
           throw new \Exception("Công ty chưa cài đặt mã prefix cho khách hàng.(0308)", 0);
        }


        $rs = $this->model->where('pub_user_id',$pub_user_id)->where('customer_code_prefix', $customer_code_prefix)->whereHas('building.company', function($query) use ($company){
            return  $query->where('id', $company);
        })->where('type',Users::USER_APP)->orderBy('customer_code', 'desc')->first();

        if (!$rs) {

            $rs = $this->model->where('customer_code_prefix', $customer_code_prefix)->whereHas('building.company', function($query) use ($company){
                return  $query->where('id', $company);
            })->where('type',Users::USER_APP)->orderBy('customer_code', 'desc')->first();

            if (!$rs) {
                // khi khong co profile ton tai va ko co profile nao cua cong ty thi khoi dau ma bang 1
                return 1;
            }
             // khi co profile khac cua user thuoc cong ty va ko co profile cua user ton tai
            return $rs->customer_code +1;
        }

        // co ma khach hang roi
        return $rs->customer_code;
    }
    public function getUserId($ids)
    {
        return $this->model->whereIn('id', $ids)->where('type',Users::USER_WEB)->where('status', $this->model::STATUS_ACTIVE)->pluck('pub_user_id')->toArray();
    }
    public function getCustomerIds($id)
    {
        return $this->model->where('pub_user_id', $id)->where('type',Users::USER_APP)->with('bdcCustomers.bdcApartment')->where('status', $this->model::STATUS_ACTIVE)->get();
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
    public function searchByAll_v2(array $options = [],$building_id)
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
        $model = $model->where(['bdc_building_id'=>$building_id,'type'=>Users::USER_APP]);
        return $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
    }

    public static function getInfoUserById($id)
    {
        $keyCache = "getInfoUserById_" . $id;
        return Cache::remember($keyCache, 10 ,function () use ($keyCache, $id) {
            $rs = V2UserInfo::where([
                "id" => $id
            ])->first();
            if (!$rs) return null;
            return (object) $rs->toArray();
        });
    }

    public function getStaffByCompany(array $options = [],$company_id)
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

        $model = $model->where(['type' => Users::USER_WEB, 'status' => 1]);

        $model = $model->whereHas('company_staff',function($query) use($company_id){
               $query->where('bdc_company_id',$company_id);
        });

        if ($options['where']) {
            $model = $model->where($options['where']);
        }

        return $model->orderByRaw($options['order_by'])->groupBy('pub_user_id')->paginate($options['per_page']);
    }
}
