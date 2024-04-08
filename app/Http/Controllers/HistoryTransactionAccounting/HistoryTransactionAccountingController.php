<?php

namespace App\Http\Controllers\HistoryTransactionAccounting;

use App\Commons\Api;
use App\Commons\Helper;
use App\Exceptions\QueueRedis;
use App\Helpers\dBug;
use App\Models\Apartments\Apartments;
use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Repositories\BdcV2PaymentDetail\PaymentDetailRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\BuildingController;
use App\Models\BdcAccountingAccounts\AccountingAccounts;
use App\Models\BdcAccountingVouchers\AccountingVouches;
use App\Models\BdcBills\Bills;
use App\Models\BdcPaymentDetails\PaymentDetail;
use App\Models\BdcV2DebitDetail\DebitDetail;
use App\Models\CronJobManager\CronJobManager;
use App\Models\HistoryTransactionAccounting\HistoryTransactionAccounting;
use App\Models\Service\Service;
use App\Models\Vehicles\Vehicles;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\BdcApartmentServicePrice\ApartmentServicePriceRepository;
use App\Repositories\BdcCoin\BdcCoinRepository;
use App\Repositories\BdcDebitDetail\V2\DebitDetailRepository;
use App\Repositories\BdcLockCyclename\BdcLockCyclenameRepository;
use App\Repositories\BdcReceipts\V2\ReceiptRepository;
use App\Repositories\BdcV2DebitDetail\DebitDetailRepository as BdcV2DebitDetailDebitDetailRepository;
use App\Repositories\Config\ConfigRepository;
use App\Repositories\Customers\CustomersRespository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Traits\ApiResponse;
use App\Util\Debug\Log;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class HistoryTransactionAccountingController extends BuildingController
{
    use ApiResponse;
    private $modelApartment;
    private $debitDetail;
    private $_apartmentServicePriceRepository;

    public function __construct(
        Request $request,
        ApartmentsRespository $modelApartment,
        DebitDetailRepository $debitDetailRepository,
        ApartmentServicePriceRepository $apartmentServicePriceRepository
    ) {
        $this->debitDetail = $debitDetailRepository;
        $this->modelApartment = $modelApartment;
        $this->_apartmentServicePriceRepository = $apartmentServicePriceRepository;
        parent::__construct($request);
        $this->middleware('auth', ['except' => []]);
        //$this->middleware('route_permision');
        Carbon::setLocale('vi');
    }
    /**
     * Danh sách bản ghi
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
        $data['meta_title'] = 'Quản lý giao dịch tự hạch toán';
        // Phân trang
        $data['per_page'] = Cookie::get('per_page', 20);
        $data['keyword'] = $request->input('keyword', '');
        $data['filter'] = $request->all();
        if (isset($request->bdc_apartment_id) && $request->bdc_apartment_id != null) {
            $data['filter']['apartment'] = $this->modelApartment->findById($request->bdc_apartment_id);
        }
        $data['history_transaction_accountings'] = HistoryTransactionAccounting::where(['bdc_building_id' => $this->building_active_id])
            ->where(function ($query) use ($request) {
                if (isset($request->keyword) && $request->keyword != null) {
                    $query->whereHas('apartment', function ($query) use ($request) {
                            $query->where('name', 'like', '%' . $request->keyword . '%');
                    });
                }
                if (isset($request->bdc_apartment_id) && $request->bdc_apartment_id != null) {
                    $query->where('bdc_apartment_id',$request->bdc_apartment_id);
                }
                if (isset($request->from_date) && $request->from_date != null) {
                    $from_date = Carbon::parse($request->from_date)->format('Y-m-d');
                    $query->whereDate('created_at',$from_date);
                }
                if (isset($request->status) && $request->status != null) {
                    $query->where('status',$request->status);
                }
            })
            ->where('status','<>','viewed')
            ->orderBy('status')
            ->orderBy('created_at','desc')
            ->paginate($data['per_page']);
        $request->request->add(['building_id' => $this->building_active_id]);
        $last_time_update_transaction = Api::GET('payment/getLastTimePayment',$request->all());
        $data['last_time'] = $last_time_update_transaction;
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
        return view('history-transaction-accounting.index_v2', $data);
    }
    public function export(Request $request)
    { 
        $History = HistoryTransactionAccounting::where('bdc_building_id',$this->building_active_id)->where(function($query) use($request){
            if (isset($request->keyword) && $request->keyword != null) {
                $query->whereHas('apartment', function ($query) use ($request) {
                        $query->where('name', 'like', '%' . $request->keyword . '%');
                });
            }
            if (isset($request->bdc_apartment_id) && $request->bdc_apartment_id != null) {
                $query->where('bdc_apartment_id',$request->bdc_apartment_id);
            }
            if (isset($request->from_date) && $request->from_date != null) {
                $from_date = Carbon::parse($request->from_date)->format('Y-m-d');
                $query->whereDate('created_at',$from_date);
            }
            if (isset($request->status) && $request->status != null) {
                $query->where('status',$request->status);
            }
        })
        ->where('status','<>','viewed')
        ->orderBy('status')
        ->orderBy('created_at','desc')
        ->get();
        
        $result = Excel::create('Hạch toán giao dịch', function ($excel) use ($History) {
            $excel->setTitle('Hạch toán giao dịch');
            $excel->sheet('Hạch toán giao dịch', function ($sheet) use ($History) {
                $Historys = [];
               
                foreach ($History as $key => $his) {
                    $Historys[] = [
                        'STT'                => $key + 1,
                        'Mã giao dịch'       => $his->ngan_hang,
                        'Căn hộ'             => $his->customer_address,
                        'Tên khách hàng'     => $his->customer_name, 
                        'Hình thức'          => $his->type_payment == 'chuyen_khoan' ? 'Chuyển khoản' : 'tiền mặt',
                        'Số tiền nộp'        => (int)$his->cost,
                        'Tiền thừa'         =>  $his->account_balance ? (int)$his->account_balance : 0,
                        'Người tạo '         => @$his->user_created_by->email,
                        'Thời gian cập nhật '=> $his->updated_at->format('Y-m-d H:i')
                    ];
                }
                if ($Historys) {
                    $sheet->fromArray($Historys);
                }
                
            });
        })->store('xlsx',storage_path('exports/'));
        $file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
        return response()->download($file)->deleteFileAfterSend(true);
             
    }
    public function import_view()
    {
        $data['meta_title'] = 'import giao dịch tự hạch toán';
        $data['created_date'] = Carbon::now()->format('d-m-Y');
        $file   = '/downloads/import_giao_dich_hach_toan.xlsx';
        $data['file'] = $file;
        return view('history-transaction-accounting.import', $data);
    }
    public function import_vietqr(Request $request)
    {
        $data['meta_title'] = 'import giao dịch VietQR';
        $data['created_date'] = Carbon::now()->format('d-m-Y');
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
        $file   =  '/downloads/file_import_transaction_vietqr_v3.xlsx';
        $data['file'] = $file;
        return view('history-transaction-accounting.import_vietqr', $data);
    }
    public function download()
    {
        $file     = public_path() . '/downloads/import_giao_dich_hach_toan.xlsx';
        return response()->download($file);
    }
    public function download_vietqr()
    {
        $file   = public_path() . '/downloads/file_import_transaction_vietqr_v2.xlsx';
        return response()->download($file);
    }
    public function import_save(Request $request)
    {
        set_time_limit(0);
        $file = $request->file('file_import');

        if (!$file) return redirect()->route('admin.history-transaction-accounting.import')->with('warning', 'Chưa có file upload!');

        $path = $file->getRealPath();
        $excel_data = Excel::load($path)->get();

        storage_path('upload', $file->getClientOriginalName());

        $buildingId = $this->building_active_id;

        $data_list_error = array();
        
        $action = Helper::getAction();
        if ($excel_data->count()) {
            foreach ($excel_data as $content) {
                if (
                    empty($content->ma_can_ho)
                    || empty($content->so_tien)
                    || empty($content->hinh_thuc_thanh_toan)
                ) {
                    $new_content = $content->toArray();
                    $new_content['message'] = 'hãy kiểm tra lại các trường | mã căn hộ | số tiền | hình thức thanh toán| yêu cầu bắt buộc';
                    array_push($data_list_error, $new_content);
                    continue;
                }
             
             
                // check is number

                $so_tien = 0;

                if (preg_match('/\d/', $content->so_tien) !== 1) { // không phải là kiểu số hoặc nhỏ hơn 0
                    $new_content = $content->toArray();
                    $new_content['message'] = $content->so_tien . '| không phải là kiểu số nguyên';
                    array_push($data_list_error, $new_content);
                    continue;
                }

                if ($content->so_tien == 0 || $content->so_tien == '0') { // số tiền phải lớn hơn 0
                    $new_content = $content->toArray();
                    $new_content['message'] = $content->so_tien . '| số tiền phải lớn hơn 0';
                    array_push($data_list_error, $new_content);
                    continue;
                }

                $so_tien = $content->so_tien;

                // if (!empty($content->ma_dich_vu_chi_dinh) && preg_match('/\d/', $content->ma_dich_vu_chi_dinh) !== 1) { // không phải là kiểu số hoặc nhỏ hơn 0
                //     $new_content = $content->toArray();
                //     $new_content['message'] = $content->ma_dich_vu_chi_dinh . '| không phải là kiểu số nguyên';
                //     array_push($data_list_error, $new_content);
                //     continue;
                // }
                // check mã giao dịch nếu có 
                if (!empty($content->ma_giao_dich)) { // không phải là kiểu số hoặc nhỏ hơn 0
                    $check_code_transaction =  HistoryTransactionAccounting::where('ngan_hang',$content->ma_giao_dich)->where(function($query){
                          $query->where('status','da_hach_toan')
                                ->orWhere('status','cho_hach_toan');
                    })->first();
                    if($check_code_transaction){
                        $new_content = $content->toArray();
                        $new_content['message'] = $content->ma_giao_dich . '| mã giao dịch này đã tồn tại';
                        array_push($data_list_error, $new_content);
                        continue;
                    }
                }

                // check hình thức thanh toán

                if (!in_array($content->hinh_thuc_thanh_toan, ['CK', 'TM'])) { // không đúng định dạng
                    $new_content = $content->toArray();
                    $new_content['message'] = $content->hinh_thuc_thanh_toan . '| không đúng định dạng ->CK hoặc TM';
                    array_push($data_list_error, $new_content);
                    continue;
                }
                // $create_date = $content->ngay_hach_toan ? Carbon::parse(str_replace("/","-",$content->ngay_hach_toan)) : Carbon::now();
                // if (!empty($content->ngay_hach_toan) && Helper::validateDate($content->ngay_hach_toan) == false) {
                //     // Display valid date message
                //     $new_content = $content->toArray();
                //     $new_content['message'] =$content->ngay_hach_toan.'| ngày hạch toán không đúng định dạng dd/mm/yyyy';
                //     array_push($data_list_error,$new_content);
                //     continue;
                // }
                // $now = Carbon::now();
                // if (!empty($content->ngay_hach_toan) && DateTime::createFromFormat('d/m/Y', $content->ngay_hach_toan) > $now) {
                //     // Display valid date message
                //     $new_content = $content->toArray();
                //     $new_content['message'] =$content->ngay_hach_toan.'| ngày hạch toán lớn hơn ngày hiện tại';
                //     array_push($data_list_error,$new_content);
                //     continue;
                // }
                // if (!empty($content->ngay_hach_toan) && !strtotime($content->ngay_hach_toan)) {
                //     // Display valid date message
                //     $new_content = $content->toArray();
                //     $new_content['message'] =$content->ngay_hach_toan.'| ngày hạch toán không đúng định dạng dd/mm/yyyy';
                //     array_push($data_list_error,$new_content);
                //     continue;
                // }
                $now = Carbon::now();
                $create_date = $content->ngay_hach_toan ? Carbon::parse(str_replace("/","-",$content->ngay_hach_toan)) : Carbon::now();
                if ($create_date > $now) {
                    // Display valid date message
                    $new_content = $content->toArray();
                    $new_content['message'] =$content->ngay_hach_toan.'| ngày hạch toán lớn hơn ngày hiện tại';
                    array_push($data_list_error,$new_content);
                    continue;
                }
                $cycle = $create_date->format('Ym');
                // $cycle = $content->ngay_hach_toan ? DateTime::createFromFormat('d/m/Y', $content->ngay_hach_toan)->format('Ym') : null;
                if($action && $cycle){
                    $check_lock_cycle = BdcLockCyclenameRepository::checkLock($buildingId,$cycle,$action);
                    if($check_lock_cycle){
                        $new_content = $content->toArray();
                        $new_content['message'] ="Kỳ $cycle đã được khóa.";
                        array_push($data_list_error,$new_content);
                        continue;
                    }
                }
                $apartment = $this->modelApartment->findByCode($buildingId, $content->ma_can_ho); // is null : là căn hộ này không có trên hệ thống

                if (!$apartment) {
                    $new_content = $content->toArray();
                    $new_content['message'] = $content->ma_can_ho . '| căn hộ này không có trên hệ thống';
                    array_push($data_list_error, $new_content);
                    continue;
                }
                $hinh_thuc_thanh_toan = 'tien_mat';
                $kieu_phieu = 'phieu_thu';
                if ($content->hinh_thuc_thanh_toan == 'CK') { // nếu chuyển khoản
                    $hinh_thuc_thanh_toan = 'chuyen_khoan';
                    $kieu_phieu = 'phieu_bao_co';
                }

               

                $list_debit_detail = null;
                if($content->ma_dich_vu_chi_dinh && $content->ma_dich_vu_chi_dinh != 'tien_thua_khong_chi_dinh'){ // nếu mà có dịch vụ được chỉ định

                    $ApartmentServiceId = BdcV2DebitDetailDebitDetailRepository::getServiceApartment($buildingId, $apartment->id, $content->ma_dich_vu_chi_dinh);
                    if($ApartmentServiceId == 0){
                        $new_content = $content->toArray();
                        $new_content['message'] = $content->ma_dich_vu_chi_dinh . '| căn hộ chưa đăng ký dịch vụ này';
                        array_push($data_list_error, $new_content);
                        continue;
                    }
                    $debitDetail_1 = BdcV2DebitDetailDebitDetailRepository::getByApartmentIdAndServiceApartment($buildingId, $apartment->id,$content->ma_dich_vu_chi_dinh);
                    if($debitDetail_1){
                           foreach ($debitDetail_1 as $key => $value) {
                               $list_debit_detail[]=$value->toArray();
                           }
                    }
                }else{  // nếu mà không có dịch vụ được chỉ định lấy ra công nợ của căn hộ sắp xếp theo config dịch ưu tiên
                    $debitDetail_2 = BdcV2DebitDetailDebitDetailRepository::getByApartmentIdOrderByIndexAccounting($buildingId, $apartment->id);
                    if($debitDetail_2){
                        foreach ($debitDetail_2 as $key => $value) {
                            $list_debit_detail[]=$value->toArray();
                        }
                    }
                }
             
                // nếu mà không có phát sinh công nợ nào thì lập phiếu thu thừa cho căn hộ
                $tai_khoan_co = AccountingAccounts::get_detail_accountingaccount_by_accountingaccount_id('111100');
                $tai_khoan_no = AccountingAccounts::get_detail_accountingaccount_by_accountingaccount_id('131700');
                $customer = CustomersRespository::findApartmentIdV2($apartment->id,0);
                $pubUserProfile =$customer ? PublicUsersProfileRespository::getInfoUserById($customer->user_info_id) : null;

                $customerName = $customer ? @$pubUserProfile->full_name : "";
                try {
                    DB::beginTransaction();
                    $new_list_debitDetails = null;
                       
                        if($list_debit_detail){
                            if($content->ma_dich_vu_chi_dinh){ // nếu mà có dịch vụ được chỉ định
                                foreach ($list_debit_detail as $key => $value) {
                                    // lưu tạm số tiền hạch toán vào paid của debit
                                    if ($so_tien > 0) {
                                        $service_apart =  ApartmentServicePriceRepository::getInfoServiceApartmentById($value['bdc_apartment_service_price_id']);
                                        $service = $service_apart->bdc_vehicle_id > 0 ? Service::get_detail_bdc_service_by_bdc_service_id($service_apart->bdc_service_id)->name.' - '.Vehicles::get_detail_vehicle_by_id($service_apart->bdc_vehicle_id)->number : Service::get_detail_bdc_service_by_bdc_service_id($service_apart->bdc_service_id)->name;
                                       
                                        $paid_service = 0;  // số phải thu
                                        $sumery = $value['sumery'] - $value['paid'];
                                        if ($so_tien > $sumery) {
                                            $paid_service = $sumery;
                                            $so_tien -= $paid_service;
                                        } else {
                                            $paid_service = $so_tien;
                                            $so_tien -= $paid_service;
                                        }
                                        $list_debit_detail[$key]['new_paid'] = $paid_service;
                                        $list_debit_detail[$key]['name'] = $service;
                                        $new_list_debitDetails[] = $list_debit_detail[$key]; 
                                       
                                    }
                                }
                            }else{
                                foreach ($list_debit_detail as $key => $value) {
                                   
                                    if ($so_tien > 0) {
                                        $paid_service = 0;  // số phải thu
                                        $sumery = $value['sumery'] - $value['paid'];
                                        if ($so_tien > $sumery) {
                                            $paid_service = $sumery;
                                            $so_tien -= $paid_service;
                                        } else {
                                            $paid_service = $so_tien;
                                            $so_tien -= $paid_service;
                                        }
                                        $service_apart =  ApartmentServicePriceRepository::getInfoServiceApartmentById($value['bdc_apartment_service_price_id']);
                                        $service = $service_apart->bdc_vehicle_id > 0 ? Service::get_detail_bdc_service_by_bdc_service_id($service_apart->bdc_service_id)->name.' - '.Vehicles::get_detail_vehicle_by_id($service_apart->bdc_vehicle_id)->number : Service::get_detail_bdc_service_by_bdc_service_id($service_apart->bdc_service_id)->name;
                                        $list_debit_detail[$key]['new_paid'] = $paid_service;
                                        $list_debit_detail[$key]['name'] = $service;
                                        $new_list_debitDetails[] = $list_debit_detail[$key];
                                    }
                                }
                            }
                        }
                        // Log::info("tu_history_transaction_accounting", "create_date_: " . $content->ngay_hach_toan);
                        $create_date = $content->ngay_hach_toan ? Carbon::parse(str_replace("/","-",$content->ngay_hach_toan)) : Carbon::now();
                        $_historyTransactionAccounting = HistoryTransactionAccounting::create([
                            'bdc_building_id' =>$buildingId,
                            'bdc_apartment_id' =>$apartment->id,
                            'detail' => isset($new_list_debitDetails) && $content->ma_dich_vu_chi_dinh != 'tien_thua_khong_chi_dinh' ? json_encode($new_list_debitDetails) : null,  // nếu có giá trị tien_thua_khong_chi_dinh thì không lưu chi tiết công nợ
                            'customer_name' =>@$customerName,
                            'customer_address' =>$apartment->name,
                            'cost' => $content->so_tien,
                            'ngan_hang' => $content->ma_giao_dich,
                            'remark' => $content->noi_dung_chuyen_khoan,
                            'tk_no' =>$tai_khoan_co ? $tai_khoan_co->code : null,
                            'tk_co' =>$tai_khoan_no ? $tai_khoan_no->code : null,
                            'ma_khach_hang' => $apartment->ma_khach_hang,
                            'ten_khach_hang' =>$apartment->ten_khach_hang,
                            'type_payment' =>$hinh_thuc_thanh_toan,
                            'type' => $kieu_phieu,
                            'status' => 'cho_hach_toan',
                            'create_date' => $create_date,
                            'created_by' =>Auth::user()->id,
                            'account_balance' => $content->ma_dich_vu_chi_dinh != 'tien_thua_khong_chi_dinh' ? $so_tien : $content->so_tien,
                            'message' => $content->ma_dich_vu_chi_dinh ? $content->ma_dich_vu_chi_dinh : null
                        ]);

                       
                    
                    $new_content = $content->toArray();
                    $new_content['message'] = 'thêm mới thành công';
                    array_push($data_list_error, $new_content);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    $new_content = $content->toArray();
                    $new_content['message'] = $e->getMessage();
                    array_push($data_list_error, $new_content);
                    continue;
                }
            }
        }

        if ($data_list_error) {
            $result = Excel::create('Kết quả Import', function ($excel) use ($data_list_error) {

                $excel->setTitle('Kết quả Import');
                $excel->sheet('Kết quả Import', function ($sheet) use ($data_list_error) {
                    $row = 1;
                    $sheet->row($row, [
                        'STT',
                        'Căn hộ',
                        'Mã căn hộ(*)',
                        'Số tiền(*)',
                        'Hình thức thanh toán(*)',
                        'Mã dịch vụ chỉ định',
                        'Ngày hạch toán',
                        'Mã giao dịch',
                        'Nội dung chuyển khoản',
                        'Message'
                    ]);

                    foreach ($data_list_error as $key => $value) {
                        $row++;
                        $sheet->row($row, [
                            $key + 1,
                            isset($value['can_ho']) ? $value['can_ho'] : '',
                            isset($value['ma_can_ho']) ? $value['ma_can_ho'] : '',
                            isset($value['so_tien']) ? $value['so_tien'] : '',
                            isset($value['hinh_thuc_thanh_toan']) ? $value['hinh_thuc_thanh_toan'] : '',
                            isset($value['ma_dich_vu_chi_dinh']) ? $value['ma_dich_vu_chi_dinh'] : '',
                            isset($value['ngay_hach_toan']) ? $value['ngay_hach_toan'] : '',
                            isset($value['ma_giao_dich']) ? $value['ma_giao_dich'] : '',
                            isset($value['noi_dung_chuyen_khoan']) ? $value['noi_dung_chuyen_khoan'] : '',
                            $value['message'],
                        ]);
                        if (isset($value['message']) && $value['message'] == 'thêm mới thành công') {
                            $sheet->cells('J' . $row, function ($cells) {
                                $cells->setBackground('#23fa43');
                            });
                        } else {
                            $sheet->cells('J' . $row, function ($cells) {
                                $cells->setBackground('#FC2F03');
                            });
                        }
                    }
                });
            })->store('xlsx',storage_path('exports/'));
            ob_end_clean();
            Log::info("import_save2" ,520);
            $file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
            return response()->download($file)->deleteFileAfterSend(true);
             
        }
    }
    public function confirm_transaction(Request $request)
    {
        $request->request->add(['building_id' => $this->building_active_id]);
        dBug::trackingPhpErrorV2($request->all());
        $result = Api::POST('admin/createPaymentHistory', $request->all());
        if($result->status == true){
            return $this->sendSuccessApi([],200,'Cập nhật thành công.'); 
        }else{
            return $this->sendSuccessApi([],200,$result->mess); 
        }
    }
    public function action(Request $request, ReceiptRepository $receiptRepository, ConfigRepository $config)
    {
        $method = $request->input('method','');
        if ($method == 'per_page') {
            $this->per_page($request);
            return back();
        }else if($method == 'huy_hach_toan') {
            $ids    = $request->input('ids', []);
            $list_transaction_accounting = HistoryTransactionAccounting::whereIn('id',$ids)->where('status','cho_hach_toan')->update(['status'=>'huy_hach_toan']);
            return back()->with('success', 'Hủy '.$list_transaction_accounting.' hạch toán thành công!');
        }
        else if($method == 'capnhat_ngay_hach_toan') {
            $ids    = $request->input('ids', []);
            if(count($ids) == 0 ){
                return back()->with('success', 'Bạn chưa chọn hạch toán!');
            }
            if(!isset($request->ngay_hach_toan) && $request->ngay_hach_toan == null){
                return back()->with('success', 'Bạn chưa chọn ngày hạch toán!');
            }
            $ngay_hach_toan =  Carbon::parse($request->ngay_hach_toan);
            $list_transaction_accounting = HistoryTransactionAccounting::whereIn('id',$ids)->where('status','cho_hach_toan')->update(['create_date'=>$ngay_hach_toan]);
            return back()->with('success', 'Cập nhật '.$list_transaction_accounting.'ngày hạch toán thành công!');
        }
        else if($method == 'confirm_hach_toan') {
            $ids    = $request->input('ids', []);
            if(count($ids) == 0 ){
                return back()->with('success', 'Bạn chưa chọn hạch toán!');
            }
            $list_transaction_accounting = HistoryTransactionAccounting::whereIn('id',$ids)->where('status','cho_hach_toan')->get();
            $base_url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http"). "://". @$_SERVER['HTTP_HOST'];
            $listIds = [];
            foreach ($list_transaction_accounting as $value) {
                if ($value->type_payment == 'viet_qr' || $value->type_payment == 'chuyen_khoan_auto' || $value->type_payment == 'import_transaction') {
                    $listIds[] = $value->id;
                    continue;
                }
                // lập phiếu thu
                $customer = CustomersRespository::findApartmentIdV2($value->bdc_apartment_id, 0);
                if ($value->detail) {
                    DB::beginTransaction();
                    try {
                        $detail = json_decode($value->detail);
                        // Tạo phiếu thu
                        if ($value->type === $receiptRepository::PHIEUKETOAN) {
                            $code_receipt = $receiptRepository->autoIncrementAccountingReceiptCode($config, $value->bdc_building_id);
                        } else if ($value->type === $receiptRepository::PHIEUBAOCO) {
                            $code_receipt = $receiptRepository->autoIncrementCreditTransferReceiptCode($config, $value->bdc_building_id);
                        } else if ($value->type === $receiptRepository::PHIEUCHI) {
                            $code_receipt = $receiptRepository->autoIncrementReceiptPaymentSlipCode($config, $value->bdc_building_id);
                        } else {
                            $code_receipt = $receiptRepository->autoIncrementReceiptCode($config, $value->bdc_building_id);
                        }

                        $receipt = $receiptRepository->create([
                            'bdc_apartment_id' => $value->bdc_apartment_id,
                            'bdc_building_id' => $value->bdc_building_id,
                            'receipt_code' => $code_receipt,
                            'cost' => $value->cost,
                            'cost_paid' => $value->cost,
                            'customer_name' => $value->customer_name,
                            'customer_address' => $value->customer_address,
                            'provider_address' => 'Banking',
                            'bdc_receipt_total' => 'test',
                            'description' => "",
                            'ma_khach_hang' => $value->ma_khach_hang,
                            'ten_khach_hang' =>  $value->ten_khach_hang,
                            'tai_khoan_co' => $value->tk_co,
                            'tai_khoan_no' =>  $value->tk_no,
                            'type_payment' => $value->type_payment,
                            'url' => $base_url . "/admin/receipt/getReceipt/" . $code_receipt,
                            'user_id' =>  Auth::user()->id,
                            'type' => $value->type,
                            'status' => 1,
                            'create_date' => $value->create_date ? Carbon::parse($value->create_date) : Carbon::now(),
                            'config_type_payment' => 2
                        ]);
                        $so_tien_total = $value->cost;
                        $log = null;
                        $billIds = array();
                        $debit_details = null;
                        foreach ($detail as $key => $value_detail) {
                            $debitDetail = DebitDetail::find($value_detail->id);
                            $bill_code = Bills::get_detail_bill_by_apartment_id($debitDetail->bdc_bill_id)->bill_code;
                            array_push($billIds, $bill_code);
                            $paid_service = 0;
                            $sumery = $debitDetail->sumery - $debitDetail->paid;
                            if ($sumery == 0) {
                                $value->status = 'da_hach_toan';
                                $value->user_confirm = Auth::user()->id;
                                $value->confirm_date = Carbon::now();
                                $value->save();
                            }
                            if ($so_tien_total > $sumery) {
                                $paid_service = $sumery;
                                $so_tien_total -= $paid_service;
                            } else {
                                $paid_service = $so_tien_total;
                                $so_tien_total -= $paid_service;
                            }

                            // bắt đầu hạch toán
                            $paymentdetail = PaymentDetailRepository::createPayment(
                                $value->bdc_building_id,
                                $value_detail->bdc_apartment_id,
                                $value_detail->bdc_apartment_service_price_id,
                                $value->create_date ? Carbon::parse($value->create_date)->format('Ym') : Carbon::now()->format('Ym'),
                                $value_detail->id,
                                $paid_service,
                                Carbon::now(),
                                $receipt->id,
                                0
                            );

                            $debitDetail->paid = $debitDetail->paid + $paid_service;
                            $debitDetail->save();
                            $debit_details[]=$debitDetail;
                            QueueRedis::setItemForQueue('add_queue_stat_payment_', [
                                "apartmentId" => $value_detail->bdc_apartment_id,
                                "service_price_id" => $value_detail->bdc_apartment_service_price_id,
                                "cycle_name" => $value->create_date ? Carbon::parse($value->create_date)->format('Ym') : Carbon::now()->format('Ym'),
                            ]);

                            $log[] = [
                                'debit_id' => $value_detail->id,
                                'paid' => $paid_service,
                            ];
                        }
                        // xử lý thu thừa tiền
                        if ($so_tien_total > 0) {
                            if ($value->message) {
                                $ApartmentServiceId = BdcV2DebitDetailDebitDetailRepository::getServiceApartment($value->bdc_building_id, $value_detail->bdc_apartment_id, $value->message);
                                $rsCoin = BdcCoinRepository::addCoin($value->bdc_building_id, $value_detail->bdc_apartment_id, $ApartmentServiceId, $value->create_date ? Carbon::parse($value->create_date)->format('Ym') : Carbon::now()->format('Ym'), $customer->user_info_id ?? 0, $so_tien_total, Auth::user()->id, 1, $receipt->id);
                                $paymentdetail->bdc_log_coin_id = isset($rsCoin['log']) ? $rsCoin['log'] : 0;
                            } else {
                                BdcCoinRepository::addCoin($value->bdc_building_id, $value->bdc_apartment_id, 0, $value->create_date ? Carbon::parse($value->create_date)->format('Ym') : Carbon::now()->format('Ym'), @$customer->user_info_id ?? 0, $so_tien_total, Auth::user()->id, 1, $receipt->id);
                            }
                            $receipt->account_balance = $so_tien_total;
                        }
                        $receipt->logs = $log ? json_encode($log) : null;
                        $strBillIds = serialize($billIds);
                        $receipt->bdc_bill_id = $strBillIds;
                        $receipt->metadata = json_encode($debit_details);
                        $receipt->save();
                        $value->status = 'da_hach_toan';
                        $value->user_confirm = Auth::user()->id;
                        $value->confirm_date = Carbon::now();
                        $value->save();
                        DB::commit();
                    } catch (\Exception $e) {
                        Log::info('check_import_receipt', json_encode($e->getTraceAsString()));
                        Log::info('check_import_receipt', '1_:' . json_encode($e->getLine()));
                        DB::rollBack();
                        continue;
                    }
                } else {
                    DB::beginTransaction();
                    try {
                        // Tạo phiếu thu
                        if ($value->type === $receiptRepository::PHIEUKETOAN) {
                            $code_receipt = $receiptRepository->autoIncrementAccountingReceiptCode($config, $value->bdc_building_id);
                        } else if ($value->type === $receiptRepository::PHIEUBAOCO) {
                            $code_receipt = $receiptRepository->autoIncrementCreditTransferReceiptCode($config, $value->bdc_building_id);
                        } else if ($value->type === $receiptRepository::PHIEUCHI) {
                            $code_receipt = $receiptRepository->autoIncrementReceiptPaymentSlipCode($config, $value->bdc_building_id);
                        } else {
                            $code_receipt = $receiptRepository->autoIncrementReceiptCode($config, $value->bdc_building_id);
                        }
                        $receipt = $receiptRepository->create([
                            'bdc_apartment_id' => $value->bdc_apartment_id,
                            'bdc_building_id' => $value->bdc_building_id,
                            'receipt_code' => $code_receipt,
                            'cost' => $value->cost,
                            'cost_paid' => $value->cost,
                            'customer_name' => $value->customer_name,
                            'customer_address' => $value->customer_address,
                            'provider_address' => 'Banking',
                            'bdc_receipt_total' => 'test',
                            'logs' => 'phieu_thu',
                            'description' => "",
                            'ma_khach_hang' => $value->ma_khach_hang,
                            'ten_khach_hang' =>  $value->ten_khach_hang,
                            'tai_khoan_co' => $value->tk_co,
                            'tai_khoan_no' =>  $value->tk_no,
                            'type_payment' => $value->type_payment,
                            'url' => $base_url . "/admin/receipt/getReceipt/" . $code_receipt,
                            'user_id' =>  Auth::user()->id,
                            'type' => $value->type,
                            'status' => 1,
                            'create_date' => $value->create_date ? Carbon::parse($value->create_date) : Carbon::now(),
                            'config_type_payment' => 2
                        ]);
                        // xử lý thu thừa tiền
                        if ($value->account_balance > 0) {
                            if ($value->message && $value->message != 'tien_thua_khong_chi_dinh') {
                                $ApartmentServiceId = BdcV2DebitDetailDebitDetailRepository::getServiceApartment($value->bdc_building_id, $value->bdc_apartment_id, $value->message);
                                $rsCoin = BdcCoinRepository::addCoin($value->bdc_building_id, $value->bdc_apartment_id, $ApartmentServiceId, $value->create_date ? Carbon::parse($value->create_date)->format('Ym') : Carbon::now()->format('Ym'), $customer->user_info_id ?? 0, $value->account_balance, Auth::user()->id, 1, $receipt->id);
                            } else {
                                BdcCoinRepository::addCoin($value->bdc_building_id, $value->bdc_apartment_id, 0, $value->create_date ? Carbon::parse($value->create_date)->format('Ym') : Carbon::now()->format('Ym'), @$customer->user_info_id ?? 0, $value->account_balance, Auth::user()->id, 1, $receipt->id, 'Phiếu thu khác');
                            }
                            $receipt->account_balance = $value->account_balance;
                            $receipt->save();
                        }
                        $value->status = 'da_hach_toan';
                        $value->user_confirm = Auth::user()->id;
                        $value->confirm_date = Carbon::now();
                        $value->save();
                        DB::commit();
                    } catch (\Exception $e) {
                        Log::info('check_import_receipt', '2_:' . json_encode($e->getTraceAsString()));
                        Log::info('check_import_receipt', '3_:' . json_encode($e->getLine()));
                        DB::rollBack();
                        continue;
                    }
                }
                CronJobManager::create([
                    'building_id' => $value->bdc_building_id,
                    'user_id' => auth()->user()->id,
                    'signature' => 'create_stat_payment_process:cron',
                    'status' => 0
                ]);
                       
            }
            if(count($listIds) > 0){
                $input =  [
                    'listId' => json_encode($listIds),
                    'building_id' =>  $this->building_active_id
                ];
    
                $result = Api::POST('payment/handleAccountting',$input);
    
                dBug::trackingPhpErrorV2($result);
            }
            return back()->with('success', 'Thao tác thành công!');
        }
       
    }
    public function create_debt_brick(Request $request, ConfigRepository  $config,ReceiptRepository $receiptRepository)
    {
        $method = $request->input('method','');

        if ($method == 'capnhat_ngay_hach_toan') {
            if(count($request->ids) > 0){
                $debit = $request->ids;
                $createdDate = $request->ngay_hach_toan == null ? Carbon::now() : Carbon::parse($request->ngay_hach_toan);
                foreach ($debit as $index => $item) {
                    if($item){
                        $_item =json_decode($item);
                        $receipt = null;
                        $billIds = array();
                        $debit_details = null;
                        $total_so_tien=0;
                        try {
                            DB::beginTransaction();
                            foreach ($_item as $index_1 => $item_1) {
                                if($index_1 == 0){
                                    $apartment = Apartments::get_detail_apartment_by_apartment_id($item_1->bdc_apartment_id);
                                    $_customer = CustomersRespository::findApartmentIdV2($item_1->bdc_apartment_id, 0);
                                    $pubUserProfile = $_customer ? PublicUsersProfileRespository::getInfoUserById($_customer->user_info_id) : null;
                                    $code_receipt = $receiptRepository->autoIncrementAccountingReceiptCode($config, $item_1->bdc_building_id);
                                    $receipt = $receiptRepository->create([
                                        'bdc_apartment_id' => $item_1->bdc_apartment_id,
                                        'bdc_building_id' =>  $item_1->bdc_building_id,
                                        'receipt_code' => $code_receipt,
                                        'cost' => 0,
                                        'cost_paid' => 0,
                                        'customer_name' => @$pubUserProfile->full_name,
                                        'customer_address' => @$apartment->name,
                                        'provider_address' => 'Banking',
                                        'bdc_receipt_total' => 'test',
                                        'logs' => json_encode($_item),
                                        'description' => "chủ đầu tư miễn giảm",
                                        'ma_khach_hang' => null,
                                        'ten_khach_hang' => null,
                                        'tai_khoan_co' => null,
                                        'tai_khoan_no' => null,
                                        'ngan_hang' => null,
                                        'type_payment' => 'cdt_mien_giam',
                                        'url' => $base_url . "/admin/v2/receipt/getReceipt/" . $code_receipt,
                                        'user_id' => Auth::user()->id,
                                        'type' => 'phieu_ke_toan',
                                        'status' => 1,
                                        'url_payment' => null,
                                        'create_date' => $createdDate,
                                        'config_type_payment' => 2
                                    ]);
                                }

                                $debitDetail = DebitDetail::find($item_1->id);
                                if (!$debitDetail) {
                                    continue;
                                }
                                $_bill = Bills::find($item_1->bdc_bill_id);
                                array_push($billIds, $_bill->bill_code);
                                $paid_service =  $item_1->sumery -  $item_1->paid;
                                $total_so_tien+=$paid_service;
                                PaymentDetailRepository::createPayment(
                                    $item_1->bdc_building_id,
                                    $item_1->bdc_apartment_id,
                                    $item_1->bdc_apartment_service_price_id,
                                    $createdDate->format('Ym'),
                                    $debitDetail->id,
                                    $paid_service,
                                    $createdDate,
                                    $receipt->id,
                                    0
                                );

                                $debitDetail->paid = $debitDetail->paid + $paid_service;
                                $debitDetail->save();
                                $debit_details[] = $debitDetail;
                                $_add_queue_stat_payment[] = [
                                    "apartmentId" => $apartment_id,
                                    "service_price_id" => $debitDetail->bdc_apartment_service_price_id,
                                    "cycle_name" => $createdDate->format('Ym'),
                                ];

                                if ($debitDetail->cycle_name != $createdDate->format('Ym')) { // tìm kỳ lập phiếu thu và cập nhật lại để thống kê sau này
                                    $_add_queue_stat_payment[] = [
                                        "apartmentId" => $apartment_id,
                                        "service_price_id" => $dataReceipt->apartment_service_price_id,
                                        "cycle_name" => $debitDetail->cycle_name,
                                    ];
                                }

                            }
                            $strBillIds = serialize($billIds);
                            $receipt->bdc_bill_id = $strBillIds;
                            $receipt->cost = $total_so_tien;
                            $receipt->cost_paid = $total_so_tien;
                            $receipt->metadata = json_encode($debit_details);
                            $receipt->save();
                            DB::commit();
                            if ($_add_queue_stat_payment && count($_add_queue_stat_payment) > 0) {
                                foreach ($_add_queue_stat_payment as $key => $value) {
                                    QueueRedis::setItemForQueue('add_queue_stat_payment_', $value);
                                }
                            }
                        }catch (\Exception $e){
                            \App\Commons\Util\Debug\Log::info('receipt_check_debt_brick', json_encode($e->getTraceAsString()));
                            DB::rollBack();
                        }

                    }
                }
                return back()->with('success', 'hạch toán thành công!');
            }
        }
    }
    public function indexDebtBrick(Request $request)
    {
        $data['meta_title'] = 'Quản lý gạch nợ tòa imperial';
        $data['per_page'] = Cookie::get('per_page', 10);
        if (isset($request->bdc_apartment_id) && $request->bdc_apartment_id != null) {
            $data['filter']['apartment'] = $this->modelApartment->findById($request->bdc_apartment_id);
        }
        $sql = "select bdc_v2_debit_detail.* from(select bdc_apartment_service_price.id as bdc_apartment_service_price_id from bdc_apartment_service_price inner join bdc_services on bdc_apartment_service_price.bdc_service_id=bdc_services.id where bdc_apartment_service_price.bdc_building_id=17 and bdc_apartment_service_price.deleted_at is null and bdc_services.type in (2,4)) as tb1 inner join bdc_v2_debit_detail on tb1.bdc_apartment_service_price_id = bdc_v2_debit_detail.bdc_apartment_service_price_id where bdc_v2_debit_detail.bdc_building_id=17 and bdc_v2_debit_detail.deleted_at is null and bdc_v2_debit_detail.sumery - bdc_v2_debit_detail.paid > 0 and discount =0 and cycle_name<202301 and bdc_v2_debit_detail.bdc_apartment_id in (1125,
            1126,
            1127,
            1128,
            1129,
            1130,
            1132,
            1133,
            1134,
            1136,
            1137,
            1138,
            1139,
            1140,
            1142,
            1143,
            1144,
            1145,
            1146,
            1148,
            1149,
            1150,
            1151,
            1152,
            1153,
            1154,
            1155,
            1156,
            1157,
            1158,
            1160,
            1161,
            1162,
            1163,
            1164,
            1165,
            1166,
            1167,
            1168,
            1169,
            1170,
            1171,
            1172,
            1173,
            1174,
            1175,
            1176,
            1177,
            1178,
            1179,
            1180,
            1181,
            1182,
            1183,
            1184,
            1186,
            1187,
            1188,
            1189,
            1190,
            1191,
            1192,
            1193,
            1194,
            1195,
            1196,
            1197,
            1199,
            1200,
            1201,
            1202,
            1203,
            1204,
            1205,
            1206,
            1207,
            1208,
            1209,
            1211,
            1212,
            1213,
            1214,
            1215,
            1216,
            1217,
            1218,
            1219,
            1220,
            1221,
            1222,
            1223,
            1224,
            1225,
            1226,
            1227,
            1228,
            1229,
            1230,
            1231,
            1232,
            1234,
            1235,
            1236,
            1237,
            1238,
            1239,
            1240,
            1241,
            1242,
            1243,
            1244,
            1245,
            1246,
            1247,
            1248,
            1249,
            1250,
            1251,
            1252,
            1253,
            1254,
            1255,
            1256,
            1258,
            1259,
            1260,
            1261,
            1262,
            1263,
            1264,
            1265,
            1266,
            1268,
            1269,
            1270,
            1271,
            1272,
            1273,
            1274,
            1275,
            1276,
            1277,
            1278,
            1279,
            1280,
            1281,
            1282,
            1283,
            1284,
            1285,
            1286,
            1287,
            1288,
            1289,
            1290,
            1291,
            1292,
            1294,
            1295,
            1296,
            1297,
            1298,
            1299,
            1300,
            1301,
            1303,
            1304,
            1306,
            1307,
            1308,
            1309,
            1310,
            1311,
            1312,
            1313,
            1314,
            1315,
            1316,
            1318,
            1319,
            1320,
            1321,
            1322,
            1323,
            1324,
            1325,
            1327,
            1328,
            1329,
            1330,
            1331,
            1332,
            1333,
            1334,
            1335,
            1336,
            1337,
            1338,
            1339,
            1340,
            1341,
            1342,
            1343,
            1344,
            1345,
            1346,
            1347,
            1348,
            1349,
            1351,
            1352,
            1354,
            1355,
            1356,
            1357,
            1358,
            1359,
            1360,
            1361,
            1363,
            1364,
            1366,
            1367,
            1368,
            1369,
            1370,
            1371,
            1372,
            1373,
            1375,
            1376,
            1378,
            1379,
            1380,
            1381,
            1382,
            1383,
            1384,
            1385,
            1386,
            1387,
            1390,
            1391,
            1392,
            1393,
            1394,
            1395,
            1396,
            1397,
            1402,
            1403,
            1404,
            1405,
            1406,
            1407,
            1408,
            1409,
            1411,
            1414,
            1415,
            1416,
            1418,
            1419,
            1420,
            1421,
            1423,
            1425,
            1426,
            1427,
            1428,
            1429,
            1430,
            1431,
            1432,
            1433,
            1434,
            1435,
            1436,
            2359,
            5844) order by bdc_apartment_id";
        $debits = DB::table(DB::raw("($sql) as tb_2"))->where(function ($query)use($request){
            if(isset($request->apartment_id) && $request->bdc_apartment_id!=null){
                $query->where('bdc_apartment_id',$request->bdc_apartment_id);
            }
        })->orderBy('bdc_apartment_id')->paginate($data['per_page']);
        $data['debits'] = $debits;
        $apartment_id=0;
        $list_debits =null;
        $apartment_imperial=null;
        $count=0;
        foreach ($debits as $index => $item) {
            $count++;
            if($apartment_id != $item->bdc_apartment_id){
                if($index != 0){
                    $array=[
                        'apartment_id'=>$apartment_id,
                        'list'=>$list_debits,
                    ];
                    $apartment_imperial[]=(object)$array;
                    $list_debits=[];
                }
                $apartment_id = $item->bdc_apartment_id;
            }
            if(isset($request->bdc_apartment_id) && $request->bdc_apartment_id != null && $debits->count() == $count){
                $array=[
                    'apartment_id'=>$apartment_id,
                    'list'=>$list_debits,
                ];
                $apartment_imperial[]=(object)$array;
            }
            if($apartment_id = $item->bdc_apartment_id){
                $list_debits[]=$item;
            }
        }
        $data['apartment_imperial'] = $apartment_imperial;
        return view('history-transaction-accounting.index-debt-brick', $data);
    }
    public function export_debt_brick(Request $request)
    {
        if (isset($request->bdc_apartment_id) && $request->bdc_apartment_id != null) {
            $data['filter']['apartment'] = $this->modelApartment->findById($request->bdc_apartment_id);
        }
        $sql = "select bdc_v2_debit_detail.* from(select bdc_apartment_service_price.id as bdc_apartment_service_price_id from bdc_apartment_service_price inner join bdc_services on bdc_apartment_service_price.bdc_service_id=bdc_services.id where bdc_apartment_service_price.bdc_building_id=17 and bdc_apartment_service_price.deleted_at is null and bdc_services.type in (2,4)) as tb1 inner join bdc_v2_debit_detail on tb1.bdc_apartment_service_price_id = bdc_v2_debit_detail.bdc_apartment_service_price_id where bdc_v2_debit_detail.bdc_building_id=17 and bdc_v2_debit_detail.deleted_at is null and bdc_v2_debit_detail.sumery - bdc_v2_debit_detail.paid > 0 and discount =0 and cycle_name<202301 and bdc_v2_debit_detail.bdc_apartment_id in (1125,
            1126,
            1127,
            1128,
            1129,
            1130,
            1132,
            1133,
            1134,
            1136,
            1137,
            1138,
            1139,
            1140,
            1142,
            1143,
            1144,
            1145,
            1146,
            1148,
            1149,
            1150,
            1151,
            1152,
            1153,
            1154,
            1155,
            1156,
            1157,
            1158,
            1160,
            1161,
            1162,
            1163,
            1164,
            1165,
            1166,
            1167,
            1168,
            1169,
            1170,
            1171,
            1172,
            1173,
            1174,
            1175,
            1176,
            1177,
            1178,
            1179,
            1180,
            1181,
            1182,
            1183,
            1184,
            1186,
            1187,
            1188,
            1189,
            1190,
            1191,
            1192,
            1193,
            1194,
            1195,
            1196,
            1197,
            1199,
            1200,
            1201,
            1202,
            1203,
            1204,
            1205,
            1206,
            1207,
            1208,
            1209,
            1211,
            1212,
            1213,
            1214,
            1215,
            1216,
            1217,
            1218,
            1219,
            1220,
            1221,
            1222,
            1223,
            1224,
            1225,
            1226,
            1227,
            1228,
            1229,
            1230,
            1231,
            1232,
            1234,
            1235,
            1236,
            1237,
            1238,
            1239,
            1240,
            1241,
            1242,
            1243,
            1244,
            1245,
            1246,
            1247,
            1248,
            1249,
            1250,
            1251,
            1252,
            1253,
            1254,
            1255,
            1256,
            1258,
            1259,
            1260,
            1261,
            1262,
            1263,
            1264,
            1265,
            1266,
            1268,
            1269,
            1270,
            1271,
            1272,
            1273,
            1274,
            1275,
            1276,
            1277,
            1278,
            1279,
            1280,
            1281,
            1282,
            1283,
            1284,
            1285,
            1286,
            1287,
            1288,
            1289,
            1290,
            1291,
            1292,
            1294,
            1295,
            1296,
            1297,
            1298,
            1299,
            1300,
            1301,
            1303,
            1304,
            1306,
            1307,
            1308,
            1309,
            1310,
            1311,
            1312,
            1313,
            1314,
            1315,
            1316,
            1318,
            1319,
            1320,
            1321,
            1322,
            1323,
            1324,
            1325,
            1327,
            1328,
            1329,
            1330,
            1331,
            1332,
            1333,
            1334,
            1335,
            1336,
            1337,
            1338,
            1339,
            1340,
            1341,
            1342,
            1343,
            1344,
            1345,
            1346,
            1347,
            1348,
            1349,
            1351,
            1352,
            1354,
            1355,
            1356,
            1357,
            1358,
            1359,
            1360,
            1361,
            1363,
            1364,
            1366,
            1367,
            1368,
            1369,
            1370,
            1371,
            1372,
            1373,
            1375,
            1376,
            1378,
            1379,
            1380,
            1381,
            1382,
            1383,
            1384,
            1385,
            1386,
            1387,
            1390,
            1391,
            1392,
            1393,
            1394,
            1395,
            1396,
            1397,
            1402,
            1403,
            1404,
            1405,
            1406,
            1407,
            1408,
            1409,
            1411,
            1414,
            1415,
            1416,
            1418,
            1419,
            1420,
            1421,
            1423,
            1425,
            1426,
            1427,
            1428,
            1429,
            1430,
            1431,
            1432,
            1433,
            1434,
            1435,
            1436,
            2359,
            5844) order by bdc_apartment_id";
        $debits = DB::table(DB::raw("($sql) as tb_2"))->where(function ($query)use($request){
            if(isset($request->apartment_id) && $request->bdc_apartment_id!=null){
                $query->where('bdc_apartment_id',$request->bdc_apartment_id);
            }
        })->orderBy('bdc_apartment_id')->get();
        $data['debits'] = $debits;
        $result = Excel::create('Hạch toán giao dịch', function ($excel) use ($debits) {
            $excel->setTitle('Hạch toán giao dịch');
            $excel->sheet('Hạch toán giao dịch', function ($sheet) use ($debits) {
                $Historys = [];

                foreach ($debits as $key => $value) {

                    $apartment= Apartments::get_detail_apartment_by_apartment_id($value->bdc_apartment_id);
                    $apartmentServicePrice = @$value->bdc_apartment_service_price_id != 0 ? ApartmentServicePrice::get_detail_bdc_apartment_service_price_by_apartment_id($value->bdc_apartment_service_price_id) : null;
                    $service = @$value->bdc_apartment_service_price_id != 0 ? Service::get_detail_bdc_service_by_bdc_service_id($apartmentServicePrice->bdc_service_id) : null;
                    $vehicle = @$apartmentServicePrice->bdc_vehicle_id > 0 ?Vehicles::get_detail_vehicle_by_id($apartmentServicePrice->bdc_vehicle_id) : null;
                    $Historys[] = [
                        'STT'                => $key + 1,
                        'Căn hộ'             => @$apartment->name,
                        'Dịch vụ'            => @$service->name.' - '.@$vehicle->number,
                        'Kỳ'                 => $value->cycle_name,
                        'thời gian'          => @$value->from_date .'->'. @$value->to_date,
                        'phát sinh'          => number_format(@$value->sumery),
                        'phải trả'           => number_format(@$value->sumery - @$value->paid),
                    ];
                }
                if ($Historys) {
                    $sheet->fromArray($Historys);
                }

            });
        })->store('xlsx',storage_path('exports/'));
        $file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
        return response()->download($file)->deleteFileAfterSend(true);

    }
}
