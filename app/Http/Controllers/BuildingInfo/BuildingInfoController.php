<?php

namespace App\Http\Controllers\BuildingInfo;

use App\Http\Controllers\BuildingController;
use App\Http\Requests\BuildingInfo\BuildingInfoRequest;
use App\Http\Requests\Bulding\BuildingRequest;
use App\Http\Requests\PaymentInfo\CreatePaymentRequest;
use App\Http\Requests\PaymentInfo\EditPaymentRequest;
use App\Repositories\Building\BuildingRepository;
use App\Repositories\BuildingInfo\BuildingInfoRepository;
use App\Repositories\Department\DepartmentRepository;
use App\Repositories\PaymentInfo\PaymentInfoRepository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Commons\Helper;
use App\Helpers\dBug;
use App\Models\Apartments\Apartments;
use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Models\Building\Building;
use App\Models\Building\BuildingPlace;
use App\Models\Department\Department;
use App\Models\PublicUser\V2\UserInfo;
use App\Models\Service\Service;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use ZipArchive;

class BuildingInfoController extends BuildingController
{
    protected $buildingRepository;
    protected $payment;
    protected $buildingInfo;
    protected $user_profile;
    protected $departmentRepository;
    protected $_client;

    public function __construct(
        Request $request,
        BuildingRepository $buildingRepository,
        PaymentInfoRepository $payment,
        BuildingInfoRepository $buildingInfo,
        PublicUsersProfileRespository $user_profile,
        DepartmentRepository $departmentRepository
    )
    {
        //$this->middleware('auth', ['except'=>[]]);
        //$this->middleware('route_permision');
        $this->buildingRepository = $buildingRepository;
        $this->payment = $payment;
        $this->buildingInfo = $buildingInfo;
        $this->user_profile = $user_profile;
        $this->departmentRepository = $departmentRepository;
        $_auth = [
            env('CLIENT_BANK'),
            env('CLIENT_SECRET_BANK')
        ];
        $this->_client = new \GuzzleHttp\Client(['auth' => $_auth]);

        Carbon::setLocale('vi');
        parent::__construct($request);
    }

    public function index(Request $request)
    {
        $data['meta_title'] = 'Thông tin tòa nhà';
        $data['building'] = $this->buildingRepository->getActiveBuilding($this->building_active_id);
        if($data['building']){
            $data['building_infos'] = $this->buildingInfo->findByBuilding($data['building']->id);
        }
        $data['payments'] = $this->payment->findByBuilding($data['building']->id);
        $data['list_bank_vietqr'] = Helper::list_bank_viqr();
        $data['qr_data']  = 'https://bdcadmin.dxmb.vn/audit-service?building_id='.$this->building_active_id;
        
        $request->request->add(['building_id' => $this->building_active_id]);
        $array_search='';
        $i=0;
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
        $data['method_payment'] = Helper::method_payment;
        return view('building.index', $data);
    }
    public function download_qrcode(Request $request)
    {
        $path_list = [];
        $building = Building::get_detail_building_by_building_id($this->building_active_id);
        $base_url='https://bdcadmin.dxmb.vn/audit-service?building_id='.$building->id;
        $_client = new \GuzzleHttp\Client();
        $responseTask = $_client->request('Get','https://api.qrserver.com/v1/create-qr-code/?size=250x250&data='.$base_url, ['stream' => true]);

        $body = $responseTask->getBody()->getContents();
        $base64 = base64_encode($body);
        $mime = "image/png";
        $img = ('data:' . $mime . ';base64,' . $base64);
        $file = file_get_contents($img);
        $path = storage_path('qrcode/'.@$building->name.'.png');
        $path_list[] = storage_path('qrcode/'.@$building->name.'.png');
        $file = file_put_contents($path,$file);
        $file     = storage_path('qrcode/'.@$building->name.'.png');
        return response()->download($file)->deleteFileAfterSend(true);
       
    }
    public function edit()
    {
        $data             = $this->getAttribute();
        $data['banks'] = Helper::banks();
        $data['template_emails'] = Helper::template_emails();
        $data['building'] = $this->buildingRepository->getActiveBuilding($this->building_active_id);
        $data['meta_title'] = 'Chính sửa thông tin tòa nhà';
        $data['list_bank_vietqr'] = Helper::list_bank_viqr();
        return view('building.edit', $data);
    }

    public function update(BuildingRequest $request)
    {
        $this->buildingRepository->update($request->except(['_token', 'id']), $request->id);
        Cache::store('redis')->forget( env('REDIS_PREFIX') . 'get_detail_buildingById_'.$request->id);
        return redirect('/admin/building#thong_tin_lien_he')->with('success', 'Sửa thông tin tòa nhà thành công!');
    }

    public function storePayment(CreatePaymentRequest $request)
    {
        dBug::trackingPhpErrorV2($request->all());
        $list_bank_vietqr = Helper::list_bank_viqr();
        $get_bank = null;
        foreach ($list_bank_vietqr as $key => $value) {
            if($value['name'] == $request->bank_name){
                $get_bank[]=$value;
            }
        }
        if(count($get_bank) > 0){
            $request->request->add(['short_url' => $get_bank[0]['short_url']]);
        }
        $checkBank = $this->payment->findByBankacountBuilding($request->bank_account,$this->building_active_id);
        if($checkBank){
            $responseData = [
                'success' => false,
                'message' => 'Thêm thông tin thanh toán không thành công thành công, trường Bank Số tài khoản bị trùng trong khu tòa nhà này'
            ];
        }else{
            $request->request->add(['active_payment' =>  $request->active_payment ?? 0]);
            $request->request->add(['status_payment_info' =>  $request->status_payment_info ?? 0]);
            $this->payment->create($request->except(['_token']));
            $responseData = [
                'success' => true,
                'message' => 'Thêm thông tin thanh toán thành công!'
            ];
        }
        return response()->json($responseData);
    }

    public function editPayment(Request $request)
    {
        $payment = $this->payment->findPayment($request->id);
        $list_bank_vietqr = Helper::list_bank_viqr();
        return view('building.modal.update_payment_info', compact('payment','list_bank_vietqr'));
    }

    public function updatePayment(EditPaymentRequest $request)
    {
        $list_bank_vietqr = Helper::list_bank_viqr();
        $get_bank = null;
        foreach ($list_bank_vietqr as $key => $value) {
            if($value['name'] == $request->bank_name){
                $get_bank[]=$value;
            }
        }
        if(count($get_bank) > 0){
            $request->request->add(['short_url' => $get_bank[0]['short_url']]);
        }
        $request->request->add(['active_payment' =>  $request->active_payment ?? 0]);
        $request->request->add(['status_payment_info' =>  $request->status_payment_info ?? 0]);
        $this->payment->update($request->except(['_token', 'id']), $request->id);
        $responseData = [
            'success' => true,
            'message' => 'Cập nhật thông tin thanh toán thành công!'
        ];

        return response()->json($responseData);
    }
    public function storePaymentVpBank(Request $request)
    {
        try {
            if(isset($request->id) && $request->id != null){  // update
                $array_payment_vpbank = $request->all();
                $array_payment_vpbank['b_id'] = $this->building_active_id;
                $array_payment_vpbank['active'] = 1;
                $result_InfoBank = json_decode((string)$this->_client->request('POST',env('DOMAIN_BANK').'v2/vpbank/addOrInsert',[
                    'json' => $array_payment_vpbank
                ])->getBody(), true);
    
    
            }else{  // store
                $request->except(['id']);
                $array_payment_vpbank = $request->all();
                $array_payment_vpbank['b_id'] = $this->building_active_id;
                $array_payment_vpbank['active'] = 1;
                $result_InfoBank = json_decode((string)$this->_client->request('POST',env('DOMAIN_BANK').'v2/vpbank/addOrInsert',[
                    'json' => $array_payment_vpbank
                ])->getBody(), true);
            }
            if(isset($result_InfoBank) && $result_InfoBank['success'] == true){
                return response()->json($result_InfoBank);
            }
            $responseData = [
                'success' => false,
                'message' => 'không thành công!'
            ];
    
            return response()->json($responseData);
        } catch (\Throwable $th) {
            $responseData = [
                'success' => false,
                'message' => 'không thành công!'
            ];
    
            return response()->json($responseData);
        }
        
    }

    public function storeInfoBuilding(BuildingInfoRequest $request)
    {
        $this->buildingInfo->create($request->except(['_token']));
        $responseData = [
            'success' => true,
            'message' => 'Thêm thông tin tòa nhà thành công!'
        ];

        return response()->json($responseData);
    }

    public function editInfoBuilding(Request $request)
    {
        $info = $this->buildingInfo->findBuildingInfo($request->id);
        return view('building.modal.update_building_info', compact('info'));
    }

    public function updateInfoBuilding(BuildingInfoRequest $request)
    {
        $this->buildingInfo->update($request->except(['_token', 'id']), $request->id);
        $responseData = [
            'success' => true,
            'message' => 'Sửa thông tin tòa nhà thành công!'
        ];

        return response()->json($responseData);
    }

    public function destroyPayment($id)
    {
        $this->payment->findPayment($id)->delete();
        $dataResponse = [
            'success' => true,
            'message' => 'Xóa thông tin thanh toán thành công!'
        ];
        return response()->json($dataResponse);
    }

    public function destroyInfo($id)
    {
        $this->buildingInfo->findBuildingInfo($id)->delete();
        $dataResponse = [
            'success' => true,
            'message' => 'Xóa thông tin tòa nhà thành công!'
        ];
        return response()->json($dataResponse);
    }

    private function getAttribute()
    {
        return [
            'departments'         => $this->departmentRepository->findByBuildingId($this->building_active_id),
            'managers'            => $this->user_profile->findByBuildingId($this->building_active_id),
        ];
    }

    public function changeBuilding(Request $request) {
        $this->setBuildingIdActive($request->building_id, \Auth::guard()->user()->id);
        return [
            'success' => true,
            'message' => 'Thay đổi tòa nhà thành công'
        ];
    }
    public function setBuildingId(Request $request) {
        $this->setBuildingIdActive($request->building_id, \Auth::guard()->user()->id);
        return [
            'success' => true,
            'message' => 'Thay đổi tòa nhà thành công',
        ];
    }
}
