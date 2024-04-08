<?php

namespace App\Http\Controllers;

use App\Commons\Api;
use Illuminate\Http\Request;
use App\Services\AppConfig;
use App\Services\AppUserPermissions;
use App\Commons\Helper;
use App\Helpers\dBug;
use App\Models\Apartments\V2\UserApartments;
use App\Models\Vnpay\VnpayReturnLog;
use Carbon\Carbon;
use App\Models\PublicUser\UserInfo;
use App\Models\Building\Building;
use App\Models\Campain;
use App\Services\ServiceSendMailV2;
use App\Util\Debug\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

class BuildingController extends Controller
{
    public $building_active_id;
    public $buildings;
    public $app_id;
    public $regency;  //chức vụ
    public $access_router;  // danh sách quyền user
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request, $is_api = null)
    {
        $user = Auth::guard('backend_public')->user();
        if (Auth::guard('backend_public')->user() && $is_api === null) {

            // phan nay do to tool noi bo set app id tuong ung
            $this->app_id = AppConfig::getAppIdOfDomain($request->getHost());

            // kiêm tra app id đúng hay không?
            if (!$this->checkAppId($this->app_id)) {
                return  abort(403, 'Bạn không thể thao tác với app id này. <'.$this->app_id.'>');
            }
           
            $this->building_active_id  = $this->getBuildingIdActive( $user->id);
            $this->buildings  = $this->getBuildings( $this->app_id,  $user->id );


            if (!$this->building_active_id) {
                $profile_user_first = UserInfo::where(['pub_user_id'=>$user->id,'type'=>2,'status'=>1])->first();
                if($profile_user_first)
                {  
                   $this->building_active_id = $profile_user_first->bdc_building_id; 
                   $this->setBuildingIdActive( $profile_user_first->bdc_building_id,$user->id);
                }
            }
            $this->access_router = AppUserPermissions::getAccessRouter($user);
            \View::share('user_access_router', $this->access_router);
            \View::share('building_id', $this->building_active_id);

        }
        if ($is_api == 'app') {
            $user = Auth::guard('public_user_v2')->user();
            // phan nay do to tool noi bo set app id tuong ung
            $this->app_id = AppConfig::getAppIdOfDomain($request->getHost());

            // kiêm tra app id đúng hay không?
            if (!$this->checkAppId($this->app_id)) {
                return  abort(403, 'Bạn không thể thao tác với app id này. <'.$this->app_id.'>');
            }
            
            if (@$request->building_id && $user) {

                $this->buildings  = $this->getBuildings( $this->app_id, $user->id , 'app');
                $building = $this->buildings[$request->building_id];
                // kiem tra id toa nha gui len co nam trong danh sach toa nha cua user co the access ko.
                if (!isset($building)) {
                    throw new \Exception("Người dùng không có căn hộ trong tòa nhà này(1112).", 1112);
                }
                $this->building_active_id  = $request->building_id;
                $this->setBuildingIdActive( $request->building_id, $user->id );
            }

        }


    }

    private function getBuildings($app_id, $user_id, $type=null)
    {
        $result = [];
        if(Auth::guard('backend_public')->user()){
             if (Helper::checkAdmin($user_id) || Auth::user()->isadmin == 1 ) {
                //neu la admin thi lay all toa nha. thay doi ngay 03/02/20120 do NganDao de xuat
                $rs = Building::where('status',1)->orderBy('created_at','desc')->select('name', 'id')->get();
                foreach ($rs as $key => $value) {
                    $result[$value->id] = $value->name;
                }
            }else{
                if ($type =='app') {
                    $rs = Auth::guard('public_user')->user()->info()->with(['building'=>function($query){
                        $query->where('status',1);
                        $query->select('name', 'id');
                    }])->get();
                }else{
                    $rs = Auth::user()->infoWeb()->with(['building'=>function($query){
                        $query->where('status',1);
                        $query->select('name', 'id');
                    }])->get();
                }


                foreach ($rs as $key => $value) {
                    if( $value->building ) {
                        $result[$value->building->id] = $value->building->name;
                    }
                }
            }
        }else{
           
            $info = Auth::guard('public_user_v2')->user()->infoApp;

            $buildingIds = UserApartments::whereHas('building')->where('user_info_id',$info->id)->pluck('building_id');

            if(count($buildingIds) > 0){
                foreach ($buildingIds as $value) {
                    $result[$value] = Building::get_detail_building_by_building_id($value)->name;
                }
            }
          
        
        }
       

        return $result;
    }
    public function getWorkDiary($user_id)
    {
        return Cache::store('redis')->get(env('REDIS_PREFIX') . '_DXMB_WORKDIARY' . $user_id);
    }
    public function setWorkDiary($user_id, $data_workdiary)
    {
        return Cache::store('redis')->put(env('REDIS_PREFIX') . '_DXMB_WORKDIARY' . $user_id, $data_workdiary);
    }
    public function getPagination()
    {
        return Cache::store('redis')->get(env('REDIS_PREFIX') . '_DXMB_PAGINATION');
    }
    public function setPagination($pagination)
    {
        return Cache::store('redis')->put(env('REDIS_PREFIX') . '_DXMB_PAGINATION', $pagination);
    }
    public function get_check_duplicate_receipt($user_id)
    {
        return Cache::store('redis')->get(env('REDIS_PREFIX') . '_DXMB_RECEIPT' . $user_id);
    }
    public function set_check_duplicate_receipt($user_id, $data_receipt)
    {
        return Cache::store('redis')->put(env('REDIS_PREFIX') . '_DXMB_RECEIPT' . $user_id, $data_receipt);
    }
    public static function del_check_duplicate_receipt($user_id)
    {
        return Cache::store('redis')->forget(env('REDIS_PREFIX') . '_DXMB_RECEIPT' . $user_id);
    }
    public function setBuildingIdActive($bdc_active_id, $user_id)
    {
        return Cache::store('redis')->put( env('REDIS_PREFIX') . '_DXMB_BUILDING_ACTIVE'.$user_id , $bdc_active_id );
    }

    public function getBuildingIdActive($user_id)
    {
        return Cache::store('redis')->get( env('REDIS_PREFIX') . '_DXMB_BUILDING_ACTIVE'.$user_id);
    }

    public function delBuildingIdActive($user_id)
    {
        return Cache::store('redis')->forget( env('REDIS_PREFIX') . '_DXMB_BUILDING_ACTIVE'.$user_id);
    }

    public function per_page(Request $request)
    {
        $per_page = $request->input('per_page', 20);

        Cookie::queue('per_page', $per_page, 60 * 24 * 30);

        return back();
    }

    private function checkAppId($app_id)
    {
        return AppConfig::hasAppId($app_id);
    }
    public function createPayment($code, $cost, $order_desc, $type = 1, $buildingId = null, $bank = null){
        $order_type = '250006';
        $language = 'vn';
        $vnp_Amount = $cost;

        $_buildingId = $buildingId != null ? $buildingId : $this->building_active_id;
        $BuildingInfo = $this->getVNPayInfo($_buildingId);
        
        $client = new \GuzzleHttp\Client();
            $headers = [
                'ClientSecret' =>env('ClientSecret_PAYMENT'),
                'ClientId' => env('ClientId_PAYMENT'),
                'HashKey' => $BuildingInfo['vnp_secret'] ?? null,
            ];
            $array_payment = [
                'order_id'=> $code,
                'order_desc'=> $order_desc,
                'order_type'=> $order_type,
                'amount'=> $vnp_Amount,
                'bank_code'=> $bank ?? null,
                'language'=> $language,
                'ip_address'=> $_SERVER['REMOTE_ADDR'],
                'redirect_uri'=> $type == 1 ? env('vnp_Returnurl') : env('vnp_Returnurl_app'),
            ];
            try {
                $requestClient = $client->request('POST',  env('API_PAYMENT'), [
                    'headers' => $headers,
                    'json' => $array_payment,
                ]);
                $result_resource = json_decode((string) $requestClient->getBody(), true);

                VnpayReturnLog::create([
                    'messages' => "Thành công!",
                    'receipt_code' => $type == 1 ? $code .'|vnp_Returnurl: '.env('vnp_Returnurl') : $code .'|vnp_Returnurl: '.env('vnp_Returnurl_app').'| '.$result_resource['data'],
                    'receipt' =>  $code,
                    'status' =>  0,
                    'building_id' => (int)$_buildingId,
                    'content' => json_encode($array_payment),
                    'created_date' => Carbon::now()->toDateTimeString(),
               ]);
                return $result_resource['data'];
            } catch (\Exception $e) {

               VnpayReturnLog::create([
                    'messages' => $e->getMessage(),
                    'receipt_code' => $type == 1 ? $code .'|vnp_Returnurl: '.env('vnp_Returnurl') : $code .'|vnp_Returnurl: '.env('vnp_Returnurl_app'),
                    'receipt' =>  null,
                    'status' =>  0,
                    'building_id' => (int)$_buildingId,
                    'content' => json_encode($array_payment),
                    'created_date' => Carbon::now()->toDateTimeString(),
               ] );

            }
        return NULL;
    }
    public function sendMailAll(array $params,$email,$type,$status)
    {
        $total = ['email'=>1, 'app'=> 0, 'sms'=> 0];
        $campain = Campain::updateOrCreateCampain($email, $type,null, $total, $this->building_active_id,0, 0);
         
        $data = [
            'params' => $params,
            'cc' => $email,
            'building_id' => $this->building_active_id,
            'type' => $type,
            'status' => $status,
            'campain_id' => $campain->id,
        ];
        try {
            ServiceSendMailV2::setItemForQueue($data);
            return ;
        } catch (\Exception $e)
        {
            return $e->getMessage();
        }
    }

    public function getVNPayInfo($building_id)
    {
         return app('App\Repositories\Building\BuildingRepository')->find($building_id);
    }
    public function getBuilding($building_id)
    {
         return app('App\Repositories\Building\BuildingRepository')->find($building_id);
    }
    public function getAllBuilding()
    {
         return app('App\Repositories\Building\BuildingRepository')->All();
    }

}
