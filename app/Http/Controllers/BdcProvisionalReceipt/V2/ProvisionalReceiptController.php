<?php

namespace App\Http\Controllers\BdcProvisionalReceipt\V2;

use App\Commons\Helper;
use App\Commons\Util\Debug\Log;
use App\Http\Controllers\BuildingController;
use App\Repositories\BdcLockCyclename\BdcLockCyclenameRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProvisionalReceipt\ProvisionalReceiptRequest;
use App\Models\Apartments\Apartments;
use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\BdcProvisionalReceipt\ProvisionalReceiptRepository;
use App\Repositories\BdcReceipts\ReceiptRepository;
use App\Repositories\Config\ConfigRepository;
use Carbon\Carbon;
use App\Models\Building\Building;
use App\Repositories\BdcCoin\BdcCoinRepository;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProvisionalReceiptController extends BuildingController
{
    protected $model;

    public function __construct(Request $request, ProvisionalReceiptRepository $provisionalReceiptRepository)
    {
        //$this->middleware('auth', ['except'=>[]]);
        //$this->middleware('route_permision');
        $this->model = $provisionalReceiptRepository;
        Carbon::setLocale('vi');
        parent::__construct($request);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request,ApartmentsRespository $apartmentsRespository, ConfigRepository $configRepository)
    {
        $apartments = $apartmentsRespository->findByBuildingId($this->building_active_id);
        if(isset($request->type) && $request->type='receipt_deposit'){
            $configs = $configRepository->findByKey($this->building_active_id, $configRepository::RECEIPT_DEPOSIT);
        }else{
            $configs = $configRepository->findByKey($this->building_active_id, $configRepository::PROVISIONAL_RECEIPT);
        }
        return view('provisional_receipt.v2.create', ['meta_title'=> 'Tạo Phiếu thu khác', 'apartments' => $apartments, 'configs' => $configs,'type'=>$request->type]);
    }

    public function createPaymentSlip(Request $request,ApartmentsRespository $apartmentsRespository, ConfigRepository $configRepository)
    {
        $apartments = $apartmentsRespository->findByBuildingId($this->building_active_id);
        if(isset($request->type) && $request->type='receipt_payment_deposit'){
            $configs = $configRepository->findByKey($this->building_active_id, $configRepository::RECEIPT_PAYMENT_DEPOSIT);
        }else{
            $configs = $configRepository->findByKey($this->building_active_id, $configRepository::RECEIPT_PAYMENT_SLIP);
        }
        return view('provisional_receipt.v2.create_payment_slip', ['meta_title'=> 'Tạo phiếu chi', 'apartments' => $apartments, 'configs' => $configs,'type'=>$request->type]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProvisionalReceiptRequest $request, ReceiptRepository $receiptRepository, ConfigRepository $config)
    {
        $input = $request->all();
        if(Carbon::parse($input['create_date']) > Carbon::now()->addDays(1)) {
            return redirect()->back()->with('warning', 'Ngày hạch toán không được lớn hơn ngày lập phiếu');
        }
        $base_url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http"). "://". @$_SERVER['HTTP_HOST'];
        $buildingId = Apartments::get_detail_apartment_by_apartment_id($input['apartment_id'])->building_id;
        $cycle = Carbon::parse($request->create_date)->format('Ym');
        $action = Helper::getAction();
        if ($action) {
            $check_lock_cycle = BdcLockCyclenameRepository::checkLock($this->building_active_id, $cycle, $action);
            if ($check_lock_cycle) {
                if(isset($request->type) && ($request->type == 'receipt_deposit')){
                    return redirect('admin/v2/provisional-receipt/create?type=receipt_deposit')->with('warning',  "Kỳ $cycle đã được khóa.");
                }else{
                    return redirect('admin/v2/provisional-receipt/create')->with('warning',  "Kỳ $cycle đã được khóa.");
                }
            }
        }
        DB::beginTransaction();
        try {
            if(isset($request->type) && ($request->type == 'receipt_deposit')){
                $data['feature'] = 'deposit';
                $data['type'] = $receiptRepository::PHIEUTHU_KYQUY;
                $data['receipt_code'] = $receiptRepository->autoIncrementReceiptDeposit($config, $buildingId);
            }else{
                $data['receipt_code'] = $receiptRepository->autoIncrementReceiptCodePrevious($config, $buildingId);
                $data['type'] = $receiptRepository::PHIEUTHU_TRUOC;
            }
            $paidInt = str_replace(',', '', $input['customer_paid']);
            $data['bdc_building_id'] = $buildingId;
            $data['bdc_apartment_id'] = $input['apartment_id'];
            $data['config_id'] = $input['config_id'];
            $data['user_id'] = Auth::id();
            $data['customer_name'] = $input['customer_fullname'];
            $data['type_payment'] = $input['payment_type'];
            $data['ma_khach_hang'] = $input['ma_khach_hang'];
            $data['ten_khach_hang'] = $input['ten_khach_hang'];
            $data['cost'] = $paidInt;
            $data['description'] = $input['customer_description'];
            $data['status'] = $this->model::NOTCOMPLETED;
            $data['create_date'] = $request->create_date ? Carbon::parse($request->create_date)->format('Y-m-d') : Carbon::now()->format('Y-m-d');
            $data['url'] =   $base_url."/admin/receipt/getReceipt/".$data['receipt_code'];
            $data['vnp_transactionno'] = 'phieu_thu_chi';
            $data['config_type_payment'] = 2;
            $receipt= $receiptRepository->create($data);
            DB::commit();
            } catch (\Exception $e) {
                Log::info('tu_check',$e->getLine());
                Log::info('tu_check_1',$e->getTraceAsString());
                DB::rollBack();
                throw new \Exception("register ERROR: ". $e->getMessage(), 1);
            }
       
            if(isset($request->type) && ($request->type == 'receipt_deposit')){
                return redirect('admin/v2/provisional-receipt/create?type=receipt_deposit')->with('success', 'Thêm thông tin phiếu thu ký quỹ thành công.');
            }else{
                return redirect('admin/v2/provisional-receipt/create')->with('success', 'Thêm thông tin phiếu thu tạm thành công.');
            }
            

    }

    public function storePaymentSlip(ProvisionalReceiptRequest $request, ReceiptRepository $receiptRepository, ConfigRepository $config)
    {
        $input = $request->all();
        $base_url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http"). "://". @$_SERVER['HTTP_HOST'];
        if(Carbon::parse($input['create_date']) > Carbon::now()->addDays(1)) {
            return redirect()->back()->with('warning', 'Ngày hạch toán không được lớn hơn ngày lập phiếu');
        }
        $buildingId = Apartments::get_detail_apartment_by_apartment_id($input['apartment_id'])->building_id;
        $cycle = Carbon::parse($request->create_date)->format('Ym');
        $action = Helper::getAction();
        if ($action) {
            $check_lock_cycle = BdcLockCyclenameRepository::checkLock($this->building_active_id, $cycle, $action);
            if ($check_lock_cycle) {
                if(isset($request->type) && ($request->type == 'receipt_payment_deposit')){
                    return redirect('admin/v2/provisional-receipt/create-payment-slip?type=receipt_payment_deposit')->with('warning', "Kỳ $cycle đã được khóa.");
                }else{
                    return redirect('admin/v2/provisional-receipt/create-payment-slip')->with('warning', "Kỳ $cycle đã được khóa.");
                }
            }
        }
        DB::beginTransaction();
        try {
            if(isset($request->type) && ($request->type == 'receipt_payment_deposit')){
                $data['feature'] = 'deposit';
                $data['type'] = $receiptRepository::PHIEUHOAN_KYQUY;
                $data['receipt_code'] = $receiptRepository->autoIncrementReceiptPaymentSlipDeposit($config, $buildingId);
            }else{
                $data['type'] = $receiptRepository::PHIEUCHIKHAC;
                $data['receipt_code'] = $receiptRepository->autoIncrementReceiptPaymentSlipCodeOther($config, $buildingId);
            }
            $paidInt = str_replace(',', '', $input['customer_paid']);
            $data['bdc_building_id'] = $buildingId;
            $data['bdc_apartment_id'] = $input['apartment_id'];
            $data['config_id'] = $input['config_id'];
            $data['user_id'] = Auth::id();
            $data['customer_name'] = $input['customer_fullname'];
            $data['type_payment'] = $input['payment_type'];
            $data['cost'] = $paidInt;
            $data['description'] = $input['customer_description'];
            $data['status'] = $this->model::COMPLETED;
            $data['create_date'] = $request->create_date ? Carbon::parse($request->create_date)->format('Y-m-d') : Carbon::now()->format('Y-m-d');
            $data['url'] =  $base_url."/admin/receipt/getReceipt/".$data['receipt_code'];
            $data['vnp_transactionno'] = 'phieu_thu_chi';
            $data['config_type_payment'] = 2;
            $receipt = $receiptRepository->create($data);
            DB::commit();
        } catch (\Exception $e) {
                  DB::rollBack();
                  throw new \Exception("register ERROR: ". $e->getMessage(), 1);
        }
        if(isset($request->type) && ($request->type == 'receipt_payment_deposit')){
            return redirect('admin/v2/provisional-receipt/create-payment-slip?type=receipt_payment_deposit')->with('success', 'Thêm thông tin phiếu hoàn ký quỹ thành công.');
        }else{
            return redirect('admin/v2/provisional-receipt/create-payment-slip')->with('success', 'Thêm thông tin phiếu chi thành công.');
        }
    }
}
