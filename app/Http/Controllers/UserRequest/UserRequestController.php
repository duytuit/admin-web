<?php

namespace App\Http\Controllers\UserRequest;

use App\Commons\Api;
use App\Commons\Helper;
use App\Helpers\dBug;
use App\Http\Controllers\BuildingController;
use App\Models\Campain;
use App\Models\UserRequest\UserRequest;
use App\Models\Vehicles\Vehicles;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Services\FCM\SendNotifyFCMService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

class UserRequestController extends BuildingController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private $model;
    private $modelUserProfile;
    private $modelApartment;


    public function __construct( PublicUsersProfileRespository $modelUserProfile, ApartmentsRespository $modelApartment,Request $request)
    {
  
        $this->modelUserProfile = $modelUserProfile;
        $this->modelApartment = $modelApartment;
        parent::__construct($request);
    }
    
    public function registerVehicle(Request $request)
    {
        $data['heading']    = 'Ý kiến phản hồi';
        $data['meta_title'] = "QL Ý kiến phản hồi";
        $data['per_page'] = Cookie::get('per_page', 20);
        $data['filter'] = $request->all();
        if(isset($data['filter']['apartment_id'])){
            $data['get_apartment'] = $this->modelApartment->findById($data['filter']['apartment_id']);
         }
        $data['registerVehicle'] = UserRequest::where('building_id', $this->building_active_id)->where(function ($query) use ($request) {
            if (isset($request->keyword) && $request->keyword) {
                $vehicle = Vehicles::where('number','like','%'.$request->keyword.'%')->first();
                $query->where('data','like','%'.$request->keyword.'%')
                      ->orwhere('data','like','%'.$vehicle->id.'%');
            }
            if (isset($request->apartment_id) && $request->apartment_id) {
                $query->where('apartment_id', $request->apartment_id);
            }
            if (isset($request->type) && $request->type) {
                $query->where('type', $request->type);
            }
            if (isset($request->user_id) && $request->user_id) {
                $query->where('user_id', $request->user_id);
            }
            if ($request->status != null) {
                if( $request->status == 0){
                    $query->whereIn('status',[0,5]);
                }else{
                    $query->where('status', $request->status);
                }
            }
        })->orderBy('updated_at', 'desc')->paginate($data['per_page']);
        return view('user-request.index', $data);
    }
    public function detail_comments(Request $request,$id)
    {
        $data['heading']    = 'Phản hồi';
        $data['meta_title'] = "Ý kiến phản hồi";
        $data['per_page'] = Cookie::get('per_page', 20);
        $user_request = UserRequest::find($id);
        if(!$user_request){
            return back()->with('error', 'Không tìm thấy yêu cầu');
        }
        $request->request->add(['building_id' => $this->building_active_id]);
        $request->request->add(['id_request' => $id]);
        $result_list = Api::GET('admin/getListUserReqComment',$request->all());
        $data['user_request'] = $user_request;
       
        $data['colors']   = ['#008a00', '#0050ef', '#6a00ff', '#a20025', '#fa6800', '#825a2c', '#6d8764'];
        $data['now']      = Carbon::now();
        $array_search='';
        $i=0;
        $request->request->add(['building_id' => $this->building_active_id]);
        foreach ($request->all() as $key => $value) {
            if($i == 0){
                $param='?'.$key.'='.(string)$value;
            }else{
                $param='&'.$key.'='.(string)$value;
            }
            $i++;
            $array_search.=$param;
        }
        $data['array_search'] = $array_search;
        $vehicleCateActive = DB::table('bdc_vehicles_category')
        ->where('bdc_building_id', $this->building_active_id)
        ->where('status', 1)
        ->whereNotNull('bdc_service_id')
        ->whereNull('deleted_at')
        ->get();

        if($user_request->status === 0){
            $request->request->add(['status' => 1]);
            $rs = Api::POST('admin/updateStatusUserRequest',$request->all());
        }

         $data['vehicleCateActive'] = $vehicleCateActive;
        if($result_list->status == true){
            $data['comment_lists'] = $result_list->data;
            $comment_lists =collect($result_list->data)->sortBy('id');
            $data['user_request_revert'] = $comment_lists;
            return view('user-request.comments', $data);
        }else{
            return back()->with('error', $result_list->mess);
        }
       
    }
    public function change_status(Request $request)
    {
        $userRequest = UserRequest::where('id', $request->ids)->first();
        $userRequest->status  = $request->status;
        $userRequest->save();

        $total = ['email' => 0, 'app' => 1, 'sms' => 0];
        $campain = Campain::updateOrCreateCampain('Yêu cầu '.Helper::type_user_request[$userRequest->type]. 'Trạng thái: '.Helper::status_user_request[$userRequest->status], config('typeCampain.DANG_KY_DICH_VU'), $userRequest->id, $total, $userRequest->building_id, 0, 0);
        $data_noti = [
            "message" => 'Trạng thái: '.Helper::status_user_request[$userRequest->status],
            "title" =>'Yêu cầu '. Helper::type_user_request[$userRequest->type],
            'type' => config('typeCampain.DANG_KY_DICH_VU'),
            'screen' => "PetitionSingle",
            "id" => $userRequest->id,
            "from_by"=> Auth::user()->id,
            'type_request' => $userRequest->type
        ];
        $data_noti['user_id'] =  $userRequest->user_id;
        SendNotifyFCMService::setItemForQueueNotify(array_merge($data_noti, ['building_id' =>$userRequest->building_id,'campain_id' => $campain->id,'app'=>'v2']));
        $message = [
            'error'  => 0,
            'status' => 'success',
            'msg'    => 'Đã cập nhật trạng thái!',
        ];

        return response()->json($message);
    }
}