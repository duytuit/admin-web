<?php

namespace App\Http\Controllers\Apartments\Api;

use App\Http\Controllers\BuildingController;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\Building\BuildingPlaceRepository;
use App\Repositories\Building\BuildingRepository;
use App\Repositories\Comments\CommentsRespository;
use App\Repositories\Customers\CustomersRespository;
use App\Repositories\Feedback\FeedbackRespository;
use App\Repositories\Vehicles\VehiclesRespository;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Apartments\V2\UserApartments;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ApartmentsController extends BuildingController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    use ApiResponse;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    const DUPLICATE     = 19999;
    const LOGIN_FAIL    = 10000;
    private $model;
    private $modelApartment;
    private $modelComments;
    private $modelCustomer;
    private $modelVehicle;
    private $modelBuilding;
    private $modelBuildingPlace;
    public function __construct(FeedbackRespository $model,ApartmentsRespository $modelApartment,CommentsRespository $modelComments,CustomersRespository $modelCustomer,VehiclesRespository $modelVehicle,BuildingRepository $modelBuilding, BuildingPlaceRepository $modelBuildingPlace,Request $request)
    {
        // $this->middleware('auth', ['except'=>[]]);
        $this->model = $model;
        $this->modelApartment    = $modelApartment;
        $this->modelComments = $modelComments;
        $this->modelCustomer    = $modelCustomer;
        $this->modelVehicle    = $modelVehicle;
        $this->modelBuilding    = $modelBuilding;
        $this->modelBuildingPlace = $modelBuildingPlace;
        //$this->middleware('jwt.auth');
        parent::__construct($request);
    }

    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    public function listApartment(Request $request)
    {
        $data = [];
        $per_page = $request->input('per_page', 10);
        $info = Auth::guard('public_user')->user()->BDCprofile()->where('bdc_building_id', $request->building_id)->first();
        $apartments = $this->modelApartment->findByBuildingIdPage($request, $request->building_id ?? $info->building_id, $per_page);
        foreach ($apartments as $ap){
            $data[]= [
                "id" => $ap->id,
                "name" => $ap->name,
            ];
        }
        if($data){
            return $this->responseSuccess($data);
        }
        return $this->responseError(['Không có dữ liệu.'], self::LOGIN_FAIL );
    }
    public function listApartmentv2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'building_id'      => 'required'
          ]);
    
        if ($validator->fails()) {
            return $this->validateFail($validator->errors());
        }
        if($request->search) {
            $where[] = ['name', 'like', '%' . $request->search . '%'];
            $apartments = $this->modelApartment->searchByAll_v2(['where' => $where], $request->building_id);
        }
        if($apartments){
            return $this->sendSuccessApi($apartments->toArray()['data']);
        }
        return $this->responseError(['Không có dữ liệu.'], self::LOGIN_FAIL );
    }
    public function listApartmentUser(Request $request)
    {
        $data = [];
        $per_page = $request->input('per_page', 10);
        $info = Auth::guard('public_user')->user()->BDCprofile()->where('bdc_building_id',$request->building_id)->first();
        if($request->building_id == ''){
            return $this->responseError(['Không có id tòa nhà'], self::LOGIN_FAIL );
        }
        if($request->apartment_id == ''){
            return $this->responseError(['Không có id căn hộ'], self::LOGIN_FAIL );
        }
        $users = $this->modelCustomer->findUserId($request->apartment_id,$per_page);
        foreach ($users as $u){
            $data[]= [
                "id" => $u->pub_user_profile_id,
                "name" => $u->pubUserProfile->display_name,
                "phone" => $u->pubUserProfile->phone,
                "email" => $u->pubUserProfile->email,
                "pub_user_id" => $u->pubUserProfile->pub_user_id,
                "type" => $u->type,
            ];
        }

        if($data){
            return $this->responseSuccess($data);
        }
        return $this->responseError(['Không có dữ liệu.'], self::LOGIN_FAIL );
    }
    public function detailCus(Request $request)
    {
        $data = [];

        if($request->building_id == ''){
            return $this->responseError(['Không có id tòa nhà'], self::LOGIN_FAIL );
        }
        if($request->apartment_id == ''){
            return $this->responseError(['Không có id căn hộ'], self::LOGIN_FAIL );
        }
        $data=[];$users= [];$vehicles= [];

        $apartments =  $this->modelApartment->getOneApartmentBuilding($request->apartment_id,$request->building_id);

        if($apartments){
            $customers_aprt =  UserApartments::where(['apartment_id'=>$apartments['id'],'building_id'=>$request->building_id])->get();
            $vehicles_aprt =  $this->modelVehicle->getVehicleInApartment($apartments['id']);
            $building =  $this->modelBuilding->getActiveBuilding($apartments['building_id']);
            if($apartments['building_place_id']){
                $place =  $this->modelBuildingPlace->findById($apartments['building_place_id']);
            }else{
                $place = '';
            }
            foreach ($customers_aprt as $cus){
                $users[]= [
                    'id'=>$cus->user_info_first->id,
                    'name'=>@$cus->user_info_first->full_name,
                    'phone'=>@$cus->user_info_first->phone_contact,
                    'email'=>@$cus->user_info_first->email_contact,
                    'address'=>@$cus->user_info_first->address,
                    'type'=>$cus->type,
                    'cmt'=>@$cus->user_info_first->cmt_number,
                    'cmt_nc'=>@$cus->user_info_first->cmt_date,
                    'cmt_address'=>@$cus->user_info_first->cmt_address,
                    'birthday'=> Carbon::parse(@$cus->user_info_first->birthday)->format('Y-m-d'),
                    'gender'=>@$cus->user_info_first->gender,
                    'avatar'=>url('/').'/'.@$cus->user_info_first->avatar,
                ];
            }
            foreach ($vehicles_aprt as $vh){
                $vehicles[]= [
                    'id'=>$vh->id,
                    'name'=>$vh->name,
                    'number'=>$vh->number,
                    'description'=>$vh->description,
                    'type'=>@$vh->bdc_vehicles_category->name,
                ];
            }
            $data=[
                'id'=>$apartments['id'],
                'name'=>$apartments['name'],
                'description'=>$apartments['description'],
                'floor'=>$apartments['floor'],
                'status'=>$apartments['status'],
                'area'=>$apartments['area'],
                'code'=>$apartments['code'],
                'place'=>$place?[
                    'id'=>$place->id,
                    'name'=>$place->name,
                    'address'=>$place->address,
                    'phone'=>$place->mobile,
                    'email'=>$place->email,
                    'code'=>$place->code??null,
                ]:null,
                'building'=>$building?[
                    'id'=>$building->id,
                    'name'=>$building->name,
                    'address'=>$building->address,
                    'phone'=>$building->phone,
                    'email'=>$building->email,
                ]:null,
                'users'=>$users,
                'vehicles'=>$vehicles,
            ];
            return $this->responseSuccess($data);
        }
        return $this->responseError(['Không có dữ liệu.'], self::LOGIN_FAIL );
    }

    public function upload(Request $request)
    {

        $file = $request->file;
        $directory = 'media/files';
        $name = $file->getClientOriginalName();
        $move = $file->move($directory, $name);
        return $move;
    }

    public function status(Request $request)
    {
        $data = [];
        // $info = \Auth::guard('public_user')->user()->info()->where('bdc_building_id',$request->building_id)->first();

        if($request->building_id == ''){
            return $this->responseError(['Không có id tòa nhà'], self::LOGIN_FAIL );
        }
        if($request->apartment_id == ''){
            return $this->responseError(['Không có id căn hộ'], self::LOGIN_FAIL );
        }
        if($request->status == ''){
            return $this->responseError(['Không có status'], self::LOGIN_FAIL );
        }
        $data=[];$users= [];$vehicles= [];
        $update = $this->modelApartment->updateStatus($request->apartment_id,$request->building_id,['status'=>$request->status]);
        if($update){
            return $this->responseSuccess([],'Thay đổi trạng thái căn hộ thành công.');
        }
        return $this->responseError(['Thay đổi trạng thái căn hộ không thành công'], self::LOGIN_FAIL );
    }

    public function updateData(Request $request)
    {

        $file = $request->file;
        $path = $file->getRealPath();
        $excel_data = Excel::load($path)->get();
        $apartment = $this->modelApartment->unsetApartmentSyncs($excel_data);
        $data=[];$err = [];
        foreach ($apartment['data'] as $item){
            $find_name = $this->modelApartment->findByName($item['canho']);
            if($find_name){
//                $this->modelApartment->update(['area'=>$item['dientich']],$find_name->id);
                $data[]=[
                    'index' => $item['index'],
                    'id' => $find_name->id,
                    'name' => $item['canho'],
                    'code' => $item['macan'],
                    'status' => 'ok'
                ];
            }else{
                $err[]=[
                    'index' => $item['index'],
                    'name' => $item['canho'],
                    'code' => $item['macan'],
                    'status' => 'no'
                ];
            }

        }
        foreach ($data as $item){
            $this->modelApartment->update(['code'=>$item['code']],$item['id']);
        }
       // dd(count($data),count($err),$data,$err);
    }
}
