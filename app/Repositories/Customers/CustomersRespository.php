<?php

namespace App\Repositories\Customers;

//use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Eloquent\Repository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use App\Services\ServiceSendMail;
use App\Models\Customers\Customers;
use App\Services\ServiceSendMailV2;
use App\Models\Building\Building;
use Excel;
use App\Commons\Helper;
use App\Helpers\dBug;
use App\Models\Apartments\V2\UserApartments;
use App\Models\Campain;
use App\Models\PublicUser\Users;
use App\Models\SentStatus;
use App\Util\Debug\Log;
use Illuminate\Support\Facades\DB;

class CustomersRespository extends Repository
{


    const RESIDENT = 4;
    const NOTICE_SENT = 3; // đã gửi thông báo
    const ELIGIBLE = 2; // đủ diều kiện
    const CONFIRMED = 4; // đã xác nhận
    const REFUSE = 5; // từ chối


    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\Customers\Customers::class;
    }
    public function findAllBy($colums = 'id', $id)
    {
        return $this->model->where($colums, $id)->get();
    }

    public function findApartmentId($apartmentId, $type)
    {
        return $this->model->whereHas('pubUserProfile')->where(['bdc_apartment_id' => $apartmentId, 'type' => $type])->first();
    }

    public static function findApartmentIdV2($apartmentId, $type)
    {
        $keyCache = "findApartmentById_" . $apartmentId.'_'.$type;
        return Cache::remember($keyCache, 10 ,function () use ($keyCache, $apartmentId, $type) {
            $rs = UserApartments::where(['apartment_id'=>$apartmentId,'type'=>0])->first();
            if (!$rs) return null;
            return (object) $rs->toArray();
        });
    }

    public static function findApartmentIdV3($apartmentId, $type)
    {
        return Customers::where([
                "bdc_apartment_id" => $apartmentId,
                "type" => $type
            ])->first();
    }

    public static function findResidentApartment($apartmentId, $type = null)
    {
        $rs = UserApartments::where(function ($query) use ($apartmentId, $type) {
            $query->where('apartment_id', $apartmentId);
            if (is_numeric($type)) {
                $query->where('type', $type);
            }
        })->get();
        return $rs;
    }

    public static function findResidentApartmentV2($apartmentId, $type = null, $building_id = null)
    {
        $rs = DB::table('bdc_v2_user_apartment')->where(function ($query) use ($apartmentId, $type, $building_id) {
            if ($building_id) {
                $query->where('building_id', $building_id);
            }
            if ($apartmentId && count($apartmentId) > 0) {
                $query->whereRaw("apartment_id IN ('".implode("','", $apartmentId)."')");
            }
            if (is_numeric($type)) {
                $query->where('type', $type);
            }
        })->whereNull('deleted_at');
        return $rs;
    }

    public function findUserId($apartmentId, $per_page)
    {
        return $this->model->where(['bdc_apartment_id' => $apartmentId])->with('pubUserProfile')->paginate($per_page);
    }
    public function getUserInApartment($apartmentId, $building_id)
    {
     return $this->model->whereNull('is_resident')->where(['bdc_apartment_id' => $apartmentId])->whereHas('pubUserProfile', function (Builder $query) use ($building_id){
            $query->where('bdc_building_id', $building_id);
            $query->where('type', 1);
            $query->where('status', 1);
      })->get();
    }
    public function getUserInApartmentV2($apartmentId)
    {
        return $this->model->where(['bdc_apartment_id' => $apartmentId,'type'=>0])->whereHas('pubUserProfile', function (Builder $query){

            $query->where('type', Users::USER_APP);

      })->with('pubUserProfile')->get()->toArray();
    }

    public function getUserProfileByCusBuilding($user_profile_id,$building_id)
    {
        return $this->model->whereHas('pubUserProfile', function (Builder $query) use ($building_id){

            $query->where('bdc_building_id', $building_id);

      })->where(['pub_user_profile_id' => $user_profile_id]);
    }

    public function listProfileCustomer()
    {
        return array_map(function ($item) {
            return $item['pub_user_profile_id'];
        }, $this->model->whereNull('is_resident')->select('pub_user_profile_id')->distinct('pub_user_profile_id')->get()->toArray());
    }
    public function listCustomerNew($building_id,$request,$perpage = 20)
    {
       return $this->model->whereNull('is_resident')
                            ->where(function($query) use ($request){
                                if (isset($request['type']) && $request['type'] != null) {
                                    $query->where('type', $request['type']);
                                }
                            })
                            ->whereHas('pubUserProfile', function (Builder $query) use ($request){

                                
                                    if (isset($request['from_date_search']) && $request['from_date_search'] != null) {
                                        $query->whereDate('handover_date', '>=', $request['from_date_search']);
                                    }
                                    if(!empty($request->keyword)){
                                        $query->where('display_name','like','%'.$request->keyword.'%');
                                    }
                                    if(!empty($request->email)){
                                        $query->where('email','like','%'.$request->email.'%');
                                    }
                                    if(!empty($request->phone)){
                                        $query->where('phone','like','%'.$request->phone.'%');
                                    }
                                    if(!empty($request->birthday)){
                                        $query->where('birthday', date('Y-m-d',strtotime($request->birthday)));
                                    }
                                    if(!empty($request->birthday_day) && !empty($request->birthday_month) && !empty($request->birthday_from_year) && !empty($request->birthday_to_year)){
                                        $query->whereRaw('DAY(birthday) ='.(int)$request->birthday_day);
                                        $query->whereRaw('MONTH(birthday) ='.(int)$request->birthday_month);
                                        $query->whereYear('birthday', '>=', (int)$request->birthday_from_year);
                                        $query->whereYear('birthday', '<=', (int)$request->birthday_to_year);
                                    }
                                    if(!empty($request->gender)){
                                        $query->where('gender','=',$request->gender);
                                    }


                            })->whereHas('bdcApartment', function($query) use ($request,$building_id){

                                $query->where('building_id', $building_id);

                                if (isset($request['place']) && $request['place'] != null) {
                                    $query->where('building_place_id',$request['place']);
                                }

                                if (isset($request['apartment']) && $request['apartment'] != null) {
                                    $query->where('id',$request['apartment']);
                                }

                            })->orderBy('updated_at', 'desc')->paginate($perpage);
    }
    public function listProfileCustomerNew($building_id,$request,$perpage = 20)
    {
        return $this->model->whereNotNull('status_confirm')->filter($request)->whereHas('bdcApartment', function (Builder $query) use ($building_id){

              $query->where('building_id', $building_id);

        })->Where(function ($query) use ($request) {
            if (isset($request['from_date_search']) && $request['from_date_search'] != null) {
                $query->whereDate('handover_date', '>=', $request['from_date_search']);
            }
        })->Where(function ($query) use ($request) {
            if (isset($request['to_date_search']) && $request['to_date_search'] != null) {
                $query->whereDate('handover_date', '<=', $request['to_date_search']);
            }
        })->orderBy('updated_at', 'desc')->paginate($perpage);
    }
    public function listProfileCustomerv2($building_id)
    {
        return $this->model->whereNotNull('status_confirm')->whereHas('bdcApartment', function (Builder $query) use ($building_id){

              $query->where('building_id', $building_id);

        })->get();
    }
    public function notifyCustomer($building_id, $userInfo)
    {
        return $this->model->select('id','handover_date','note_confirm')->Where(function($query){
                                $query->orWhere('status_confirm',self::NOTICE_SENT);
                          })->where(['pub_user_profile_id'=> $userInfo->id])
                          ->whereNotNull('status_confirm')
                          ->whereHas('bdcApartment', function ($query) use ($building_id){
                                  $query->where('building_id', $building_id);
                          })->get();
    }
    public function notifyCustomerConfirmed($building_id, $userInfo)
    {
        return $this->model->select('id','handover_date','note_confirm')->Where(function($query){
                                $query->orWhere('status_confirm',self::REFUSE);
                                $query->orWhere('status_confirm',self::CONFIRMED);
                                $query->orWhere('status_confirm',self::NOTICE_SENT);
                          })->where(['pub_user_profile_id'=> $userInfo->id])
                          ->whereNotNull('status_confirm')
                          ->whereHas('bdcApartment', function ($query) use ($building_id){
                                  $query->where('building_id', $building_id);
                          })->first();
    }
    public function listProfileCustomerCount($building_id)
    {
        return UserApartments::select('user_info_id')->whereHas('bdcApartment', function ($query) use ($building_id) {
            $query->where('building_id', $building_id);
        })->groupBy('user_info_id')->get()->count();
    }

    public function listProfileCustomerV3($building_id) // lấy danh sách căn hộ theo tòa
    {
        return $this->model->whereNull('is_resident')->whereHas('pubUserProfile')->whereHas('bdcApartment', function($query) use ($building_id){

            $query->where('building_id', $building_id);

        })->get();
    }

    public function getOne($colums = 'id', $id)
    {
        $row = $this->model->where($colums, $id)->first();
        $row->load('pubUserProfile');
        $row->load('bdcApartment');
        return $row;
    }
    public function searchByCustomer($request = '', $where = [], $perpage = 20)
    {

        $default = [
            'select'   => ['pub_user_profile_id', 'id', 'type', 'bdc_apartment_id'],
            'where'    => $where,
            'order_by' => 'id DESC',
            'per_page' => $perpage,
        ];

        $options = array_merge($default, $where);
        extract($options);
        $model = $this->model->select($options['select'])->distinct('pub_user_profile_id');

        if (!empty($request->apartment)) {
            $model = $model->where('bdc_apartment_id', '=', $request->apartment);
        }
        if (!empty($request->keyword)) {
            $where[] = $model->whereHas('pubUserProfile', function ($query) use ($request) {
                $query->where('display_name', 'like', '%' . $request->keyword . '%');
            });
        }
        if (!empty($request->email)) {
            $where[] = $model->whereHas('pubUserProfile', function ($query) use ($request) {
                $query->where('email', 'like', '%' . $request->email . '%');
            });
        }
        if (!empty($request->phone)) {
            $where[] = $model->whereHas('pubUserProfile', function ($query) use ($request) {
                $query->where('phone', 'like', '%' . $request->phone . '%');
            });
        }
        $list_search = $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
        $list_search->load('pubUserProfile');
        $list_search->load('bdcApartment');

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
    public function insert(array $data)
    {
        return $this->model->insert($data);
    }
    public function destroyIn(array $data)
    {
        return $this->model->destroy($data);
    }
    public function countItem($building = 0)
    {
        return UserApartments::whereHas('bdcApartment', function ($query) use ($building) {
            $query->where('building_id', $building);
        })->count();
    }

    public function checkUsersType($type, $apartment_id, $building_id)
    {
        return $this->model->where('type', $type)->where('bdc_apartment_id', $apartment_id)->whereHas('pubUserProfile', function ($query) use ($building_id) {
            $query->where('bdc_building_id', '=', $building_id);
        })->first();
    }
    public function checkUsersWithApartment($user_info_id, $apartment_code, $building_id)
    {
        return $this->model->whereHas('bdcApartment', function ($query) use ($apartment_code){
            $query->where('code', '=', $apartment_code);
        })->whereHas('pubUserProfile', function ($query) use ($building_id,$user_info_id) {
            $query->where('bdc_building_id', '=', $building_id);
            $query->where('id', '=', $user_info_id);
        })->first();
    }
    public function checkCusExit($user_profile_id, $apartment_id, $building_id)
    {
        return $this->model->where('pub_user_profile_id', $user_profile_id)->where('bdc_apartment_id', $apartment_id)->whereHas('pubUserProfile', function ($query) use ($building_id) {
            $query->where('bdc_building_id', '=', $building_id);
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
        $per_page = $request->input('per_page', 20);

        Cookie::queue('per_page', $per_page, 60 * 24 * 30);

        return back();
    }
    public function action($request)
    {
        $method = $request->input('method', '');
        if ($method == 'delete') {
            $del = $this->deleteAt($request);
            return back()->with('success', $del['msg']);
        } elseif ($method == 'status') {
            $status =  $this->status($request);
            return back()->with('success', $status['msg']);
        } elseif ($method == 'per_page') {
            return $this->per_page($request);
        }
        return back();
    }
    public function updateAllType($apt_id, $old_type, $new_type)
    {
        return $this->model->where('bdc_apartment_id', $apt_id)->where('type', $old_type)->update(['type' => $new_type]);
    }
    public function getPurchaser($apt_id)
    {
        return $this->model->where(['bdc_apartment_id'=>$apt_id, 'type' => 0])->first();
    }
    public function getCusByListApartment($apt_ids)
    {
        return $this->model->whereIn('bdc_apartment_id', $apt_ids)->select(['pub_user_profile_id'])->distinct('pub_user_profile_id')->get()->toArray();
    }
    public function getApartmentByIds($ids)
    {
        return array_map(function ($item) {
            return $item['bdc_apartment_id'];
        }, $this->model->whereIn('pub_user_profile_id', $ids)->select(['bdc_apartment_id'])->get()->toArray());
    }
    public function delCus($id, $apartment)
    {
        return $this->model->where('pub_user_profile_id', $id)->where('bdc_apartment_id', $apartment)->delete();
    }
    public function restoreCusNew($ids)
    {
        return $this->model->withTrashed()->whereIn('id',$ids)->restore();
    }
    public function delCusNew($ids)
    {
        return $this->model->whereIn('bdc_apartment_id', $ids)->delete();
    }
    public function findCusIds($ids)
    {
        return $this->model->whereIn('id', $ids)->get();
    }
    public function checkProfileApartment($id, $apartment)
    {
        return $this->model->where('pub_user_profile_id', $id)->where('bdc_apartment_id', $apartment)->first();
    }
    public function checkProfileApartmentCheckType($id, $apartment)
    {
        return $this->model->where(['bdc_apartment_id'=>$apartment,'pub_user_profile_id' =>$id])->first();
    }
    public function checkProfileApartmentV2($apartment)
    {
        return $this->model->where('bdc_apartment_id', $apartment)->first();
    }

    public function sendNotifyNewCustomer($from, $name, $building_id, $canho)
    {
         $name_building= Building::where('id',$building_id)->first()->name??'';
        if (filter_var($from, FILTER_VALIDATE_EMAIL)) {
            $total = ['email'=> 1, 'app'=> 0, 'sms'=> 0];
            $campain = Campain::updateOrCreateCampain("Gửi email cho: ".$from, config('typeCampain.RESIDENT'), null, $total, $building_id, 0, 0);

            ServiceSendMailV2::setItemForQueue([
                'params' => [
                    '@ten' => $name ?? $from,
                    '@toanha'=>$name_building,
                    '@canho'=>$canho
                ],
                'cc' => $from,
                'building_id' => $building_id,
                'type' => self::RESIDENT,
                'status' => 'create',
                'campain_id' => $campain->id
            ]);
        }
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
    public function change_status_confirm($id, $status_confirm)
    {
        $result = $this->model->find($id);
        return $result ? $result->update(['status_confirm'=>$status_confirm]) : false;
    }
    public function change_note_confirm($id, $note_confirm)
    {
        $result = $this->model->find($id);
        return $result ? $result->update(['note_confirm'=> $note_confirm]) : false;
    }
    public function findCusWithStatus_Refuse($id)
    {
        return $this->model->Where(function($query){
                                $query->orWhere('status_confirm',self::REFUSE);
                                $query->orWhere('status_confirm',self::CONFIRMED);
                          })->where(['id'=>$id])->get();
    }
    public function change_success_handover($id, $success_hangover,$is_resident)
    {
        $result = $this->model->find($id);
        return $result ? $result->update(['status_success_handover'=>$success_hangover,'is_resident'=>$is_resident]) : false;
    }
    public function change_success_handover_customer_confirm($id, $description, $success_hangover, $status_confirm, $is_resident)
    {
        $result = $this->model->find($id);
        return $result ? $result->update(['note_confirm'=> $description, 'status_success_handover'=>$success_hangover,'status_confirm'=>$status_confirm,'is_resident'=>$is_resident]) : false;
    }
    public function change_date_handover($id, $handover_date)
    {
        $result = $this->model->find($id);
        return $result ? $result->update(['handover_date'=>$handover_date]) : false;
    }
     public function ExportCustomers($building_id,$request)
    {
        $Customers =  $this->model->whereNotNull('status_confirm')->filter($request)->whereHas('bdcApartment', function (Builder $query) use ($building_id){

              $query->where('building_id', $building_id);

        })->Where(function ($query) use ($request) {
            if (isset($request['from_date_search']) && $request['from_date_search'] != null) {
                $query->whereDate('handover_date', '>=', $request['from_date_search']);
            }
        })->Where(function ($query) use ($request) {
            if (isset($request['to_date_search']) && $request['to_date_search'] != null) {
                $query->whereDate('handover_date', '<=', $request['to_date_search']);
            }
        })->orderBy('updated_at', 'desc')->get();

        $result = Excel::create('Danh sách bàn giao căn hộ', function ($excel) use ($Customers) {
            $excel->setTitle('Danh sách bàn giao căn hộ');
            $excel->sheet('Danh sách bàn giao căn hộ', function ($sheet) use ($Customers) {
                $row = 1;
                $sheet->row($row, [
                    'STT',
                    'Khách hàng',
                    'Căn hộ',
                    'Email',
                    'Số điện thoại',
                    'Địa chỉ',
                    'Ngày bàn giao dự kiến',
                    'Trạng thái xác nhận',
                    'Ghi chú',
                    'Xác nhận bàn giao'
                ]);
                foreach ($Customers as $keycus => $value) {
                    $list_apartment  = Helper::list_apartment_handover();
                    foreach ($list_apartment as $key => $value_apartment) {
                         if($value->status_confirm == $value_apartment['text']){
                            $status_confirm = $value_apartment['value'];
                            break;
                         }
                    }
                    if ($value->status_success_handover == 1) {
                        $status_success_handover = 'Đã bàn giao';
                    } else {
                        $status_success_handover = 'Chưa bàn giao';
                    }
                    $row++;
                    $sheet->row($row, [
                        ($keycus + 1),
                        @$value->pubUserProfile->display_name,
                        @$value->bdcApartment->name,
                        @$value->pubUserProfile->email,
                        @$value->pubUserProfile->phone,
                        @$value->pubUserProfile->address,
                        $value->handover_date,
                        $status_confirm,
                        $value->note_confirm,
                        $status_success_handover
                    ]);
                }
            });
        })->store('xlsx',storage_path('exports/'));
$file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
return response()->download($file)->deleteFileAfterSend(true);
             
    }
}
