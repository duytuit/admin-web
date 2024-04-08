<?php

namespace App\Http\Controllers\Receipt\Api;

use App\Http\Controllers\BuildingController;
use App\Http\Requests\ProvisionalReceipt\ProvisionalReceiptRequest;
use App\Models\BdcReceipts\Receipts;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\BdcProvisionalReceipt\ProvisionalReceiptRepository;
use App\Repositories\BdcReceipts\ReceiptRepository;
use App\Repositories\Config\ConfigRepository;
use App\Repositories\Customers\CustomersRespository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Repositories\PublicUsers\PublicUsersRespository;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\ConvertMoney;
use Illuminate\Support\Carbon;

class ReceiptAppController extends BuildingController
{
    use ApiResponse;
    /**
     * Constructor.
     */
    const DUPLICATE     = 19999;
    const LOGIN_FAIL    = 10000;
    private $model;
    private $modelApartment;
    private $modelCustomer;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(ReceiptRepository $model,ApartmentsRespository $modelApartment,CustomersRespository $modelCustomer,Request $request)
    {
        $this->model    = $model;
        $this->modelApartment    = $modelApartment;
        $this->modelCustomer    = $modelCustomer;
        //$this->middleware('jwt.auth');
        Carbon::setLocale('vi');
        parent::__construct($request);
    }

    public function index(Request $request)
    {
        $data=[];
        $info = \Auth::guard('public_user')->user()->
        BDCprofile->where('bdc_building_id',$request->building_id)->first();
        $per_page = $request->input('per_page', 10);
        $per_page = $per_page > 0 ? $per_page : 10;
        $receipt = $this->model->searchByApi($request->building_id??
            $info->bdc_building_id,$request,'',$per_page);
        $base_url=((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http"). "://". @$_SERVER['HTTP_HOST'];
        foreach ($receipt['data'] as $r){
            $data[]=[
                'id'=>$r['id'],
                'building'=>[
                    'id'=>$r['building']['id'],
                    'name'=>$r['building']['name'],
                    'phone'=>$r['building']['phone'],
                    'email'=>$r['building']['email'],
                    'company_id'=>$r['building']['company_id'],
                ],
                'apartment'=>[
                    'id'=>$r['apartment']['id'],
                    'building_id'=>$r['apartment']['building_id'],
                    'name'=>$r['apartment']['name'],
                    'floor'=>$r['apartment']['floor'],
                    'status'=>$r['apartment']['status'],
                    'area'=>$r['apartment']['area'],
                ],
                'receipt_code'=>$r['receipt_code'],
                'cost'=>$r['cost'],
                'customer_name'=>$r['customer_name'],
                'customer_address'=>$r['customer_address'],
                'provider_address'=>$r['provider_address'],
                'receipt_total'=>$r['bdc_receipt_total'],
                'url'=> $base_url."/admin/receipt/getReceipt/".$r['receipt_code'],
                'type_payment'=>$r['type_payment'],
                'description'=>$r['description'],
                'created_at'=>$r['created_at']
            ];
        }

        if($receipt){
            return $this->responseSuccess($data);
        }
        return $this->responseError(['Không có dữ liệu.'], 200,[] );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request, ReceiptRepository $receiptRepository, ProvisionalReceiptRepository $ProvisionalReceiptRepository, ConfigRepository $config)
    {
        $info = \Auth::guard('public_user')->user()->BDCprofile->where('bdc_building_id',$request->building_id)->first();
        $data['bdc_building_id'] = $request->building_id;
        $data['bdc_apartment_id'] = $request->apartment_id;
        $data['config_id'] =$request->config_id;
        $data['user_id'] = $info->id;
        $data['customer_name'] = $request->customer_name;
        $data['type_payment'] = $request->payment_type;
        $data['type'] = $receiptRepository::PHIEUTHU_TRUOC;
        $data['cost'] = $request->cost;
        $data['description'] = $request->description;
        $data['status'] = $ProvisionalReceiptRepository::NOTCOMPLETED;
        $data['receipt_code'] = $receiptRepository->autoIncrementReceiptCodePrevious($config, $request->building_id);
        $insert = $receiptRepository->create($data);

        if($insert){
            return $this->responseSuccess([],'Thêm thông tin phiếu thu tạm thành công.');
        }
        return $this->responseError(['Không có dữ liệu.'], 200,[] );
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

    public function detail(Request $request,PublicUsersRespository $users,$id)
    {
        $data=[];
        $info = \Auth::guard('public_user')->user()
            ->BDCprofile->where('bdc_building_id',$request->building_id)->first();
        if(empty($id)){
            return $this->responseError(['Không có id hóa đơn hoặc không đúng'], self::LOGIN_FAIL );
        }
        if($request->building_id == ''){
            return $this->responseError(['Không có id tòa nhà'], self::LOGIN_FAIL );
        }
        $detail = $this->model->findReceiptByIdApi($id,$request->building_id??$info->bdc_building_id);
        if(!$detail){
            return $this->responseError(['Không có dữ liệu.'], 200,[] );
        }
        $user_detail = $users->getUserById($detail['user_id']);
        if(!$user_detail){
            return $this->responseError(['Không có dữ liệu.'], 200,[] );
        }
        $numberWord = ucfirst(strtolower(ConvertMoney::NumberToWords($detail['cost'])));
        
        $data = [
            "id"=> $detail['id'],
            "receipt_code"=> $detail['receipt_code'],
            "cost"=> $detail['cost'],
            "in_word"=> $numberWord,
            "name"=> $detail['customer_name'],
            "customer_address"=> $detail['customer_address'],
            "provider_address"=> $detail['provider_address'],
            "bdc_receipt_total"=> $detail['bdc_receipt_total'],
            "logs"=> $detail['logs'],
            "type_payment"=> $detail['type_payment'],
            "description"=> $detail['description'],
            "url"=> $detail['url'],
            'created_at'=>$detail['created_at'],
            'user_create'=>[
                'id'=>@$user_detail->profileAll->id,
                'pub_user_id'=>$detail['user_id'],
                'name'=>@$user_detail->profileAll->display_name,
                'email'=>@$user_detail->profileAll->email,
                'phone'=>@$user_detail->profileAll->phone,
            ],
            'building'=>[
                'id'=>$detail['building']['id'],
                'name'=>$detail['building']['name'],
                'phone'=>$detail['building']['phone'],
                'email'=>$detail['building']['email'],
                'company_id'=>$detail['building']['company_id'],
            ],
            'apartment'=>[
                'id'=>$detail['apartment']['id'],
                'building_id'=>$detail['apartment']['building_id'],
                'name'=>$detail['apartment']['name'],
                'floor'=>$detail['apartment']['floor'],
                'status'=>$detail['apartment']['status'],
                'area'=>$detail['apartment']['area'],
            ],
        ];
        if($data){
            return $this->responseSuccess($data);
        }
        return $this->responseError(['Không có dữ liệu.'], 200,[] );
    }

    public function listApartment(Request $request)
    {
        $data = [];
        $info = \Auth::guard('public_user')->user()->BDCprofile->where('bdc_building_id',$request->building_id)->first();
        $apartments = $this->modelApartment->findByBuildingId($request->building_id??$info->building_id);
        foreach ($apartments as $ap){
            $data[]= [
                "id" => $ap->id,
                "name" => $ap->name,
            ];
        }
        if($data){
            return $this->responseSuccess($data);
        }
        return $this->responseError(['Không có dữ liệu.'], 200,[] );
    }

    public function listApartmentUser(Request $request)
    {
        $data = [];
        $info = \Auth::guard('public_user')->user()
            ->BDCprofile->where('bdc_building_id',$request->building_id)->first();
        if($request->building_id == ''){
            return $this->responseError(['Không có id tòa nhà'], self::LOGIN_FAIL );
        }
        if($request->apartment_id == ''){
            return $this->responseError(['Không có id căn hộ'], self::LOGIN_FAIL );
        }
        $user = $this->modelCustomer->findUserId($request->apartment_id,0);
        if(!$user){
            return $this->responseError(['Không có dữ liệu.'], 200,[] );
        }
        $data= [
            "id" => @$user->pub_user_profile_id,
            "name" => @$user->pubUserProfile->display_name,
            "phone" => @$user->pubUserProfile->phone,
            "email" => @$user->pubUserProfile->email,
            "pub_user_id" => @$user->pubUserProfile->pub_user_id,
        ];
        if($data){
            return $this->responseSuccess($data);
        }
        return $this->responseError(['Không có dữ liệu.'], 200,[] );
    }
    public function listConfig(Request $request, ConfigRepository $configRepository)
    {
        $data = [];
        $info = \Auth::guard('public_user')->user()->BDCprofile->where('bdc_building_id',$request->building_id)->first();
        if($request->building_id == ''){
            return $this->responseError(['Không có id tòa nhà'], self::LOGIN_FAIL );
        }
        $configs = $configRepository->findByKey($request->building_id, $configRepository::PROVISIONAL_RECEIPT);
        foreach ($configs as $c){
            $data[]= [
                "id"=>$c->id,
                "title"=>$c->title,
            ];
        }
        if($data){
            return $this->responseSuccess($data);
        }
        return $this->responseError(['Không có dữ liệu.'], 200,[] );
    }


    public function listUserReceipt(Request $request)
    {
        $data=[];
        $per_page = $request->input('per_page', 10);
        $per_page = $per_page > 0 ? $per_page : 10;
        $receipt = Receipts::select(['id', 'bdc_building_id', 'bdc_apartment_id',
                'receipt_code', 'cost', 'customer_name', 'customer_address',
                'provider_address', 'bdc_receipt_total', 'type_payment',
                'description', 'created_at'])
            ->where([
         ['bdc_building_id',"=", $request->building_id],
         ['bdc_apartment_id',"=", $request->apartment_id],
        ])->paginate($per_page);
//        dd($receipt);
        foreach ($receipt as $r){
            $data[]=[
                'id'=>$r['id'],
                'building'=>[
                    'id'=>$r['building']['id'],
                    'name'=>$r['building']['name'],
                    'phone'=>$r['building']['phone'],
                    'email'=>$r['building']['email'],
                    'company_id'=>$r['building']['company_id'],
                ],
                'apartment'=>[
                    'id'=>$r['apartment']['id'],
                    'building_id'=>$r['apartment']['building_id'],
                    'name'=>$r['apartment']['name'],
                    'floor'=>$r['apartment']['floor'],
                    'status'=>$r['apartment']['status'],
                    'area'=>$r['apartment']['area'],
                ],
                'receipt_code'=>$r['receipt_code'],
                'cost'=>$r['cost'],
                'customer_name'=>$r['customer_name'],
                'customer_address'=>$r['customer_address'],
                'provider_address'=>$r['provider_address'],
                'receipt_total'=>$r['bdc_receipt_total'],
                'type_payment'=>$r['type_payment'],
                'description'=>$r['description'],
                'created_at'=>$r['created_at']
            ];
        }

        if($receipt){
            return $this->responseSuccess($data);
        }
        return $this->responseError(['Không có dữ liệu.'], 200,[] );
    }
}
