<?php

namespace App\Http\Controllers\PaymentDetail;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BuildingController;
use App\Models\BdcAccountingVouchers\AccountingVouches;
use App\Models\BdcPaymentDetails\PaymentDetail;
use App\Models\BdcReceipts\Receipts;
use App\Models\PublicUser\Users;
use App\Models\Service\Service;
use App\Repositories\BdcApartmentServicePrice\ApartmentServicePriceRepository;
use App\Repositories\BdcDebitDetail\V2\DebitDetailRepository;
use App\Repositories\Service\ServiceRepository;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class PaymentDetailController extends BuildingController
{
    use ApiResponse;
    public $debitRepo;
    public $serviceRepo;
    public $apartmentServiceRepo;

    public function __construct(
        Request $request,
        ServiceRepository $serviceRepo,
        DebitDetailRepository $debitRepo,
        ApartmentServicePriceRepository $apartmentServiceRepo
    ) {
        $this->debitRepo = $debitRepo;
        $this->serviceRepo = $serviceRepo;
        $this->apartmentServiceRepo = $apartmentServiceRepo;
        parent::__construct($request);
        $this->middleware('auth', ['except' => []]);
        //$this->middleware('route_permision');
        Carbon::setLocale('vi');
    }

    public function index(Request $request){
        $data['meta_title'] = 'Chi tiết phiếu thu';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['filter'] = $request->all();
        $data['cycle_names'] = $this->debitRepo->getCycleNameV2($this->building_active_id);
        //dich vu dươc su dung boi can ho
        $apartmentService = $this->apartmentServiceRepo->getServiceApartment($this->building_active_id);
        $data['serviceBuildingFilter'] = $this->serviceRepo->getServiceOfApartment($apartmentService, $this->building_active_id);
        $data['payment_details'] = PaymentDetail::where(['bdc_building_id'=>$this->building_active_id])
                                            ->where(function($query) use ($request){
                                                    $query->whereHas('receipt',function($query) use ($request){
                                                        if(isset($request->keyword) && $request->keyword != null){
                                                            $query->where('customer_name','like','%'.$request->keyword.'%')
                                                                ->orwhere('receipt_code','like','%'.$request->keyword.'%');
                                                        }
                                                    })
                                                    ->orWhereHas('apartment',function($query) use ($request){
                                                        if(isset($request->keyword) && $request->keyword != null){
                                                            $query->where('name',$request->keyword);
                                                        }
                                                    });
                                            })
                                            ->where(function($query) use ($request){
                                                if(isset($request->cycle_name) && $request->cycle_name != null){
                                                    $query->where('cycle_name',$request->cycle_name);
                                                }
                                                if(isset($request->bdc_service_id) && $request->bdc_service_id != null){
                                                    $query->where('bdc_service_id',(int)$request->bdc_service_id);
                                                }
                                            })
                                            ->orderBy('created_at','desc')
                                            ->paginate($data['per_page']);
        return view('payment-detail.index', $data);
    }
    public function export(Request $request){
        $data['meta_title'] = 'Chi tiết phiếu thu';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['filter'] = $request->all();
        $payment_details = PaymentDetail::where(['bdc_building_id'=>$this->building_active_id])
                                            ->where(function($query) use ($request){
                                                    $query->whereHas('receipt',function($query) use ($request){
                                                        if(isset($request->keyword) && $request->keyword != null){
                                                            $query->where('customer_name','like','%'.$request->keyword.'%')
                                                                ->orwhere('receipt_code','like','%'.$request->keyword.'%');
                                                        }
                                                    })
                                                    ->orWhereHas('apartment',function($query) use ($request){
                                                        if(isset($request->keyword) && $request->keyword != null){
                                                            $query->where('name',$request->keyword);
                                                        }
                                                    });
                                            })
                                            ->where(function($query) use ($request){
                                                if(isset($request->cycle_name) && $request->cycle_name != null){
                                                    $query->where('cycle_name',$request->cycle_name);
                                                }
                                                if(isset($request->bdc_service_id) && $request->bdc_service_id != null){
                                                    $query->where('bdc_service_id',(int)$request->bdc_service_id);
                                                }
                                            })
                                            ->orderBy('created_at','desc')
                                            ->get();
            $result = Excel::create('danh sách chi tiết phiếu thu', function ($excel) use ($payment_details) {
                $excel->setTitle('danh sách chi tiết phiếu thu');
                $excel->sheet('danh sách', function ($sheet) use ($payment_details) {
                    foreach ($payment_details as $key => $value) {
                        $receipt = Receipts::find($value->bdc_receipt_id);
                        $service = Service::get_detail_service_by_service_id($value->bdc_service_id);
                        $user = Users::get_detail_user_by_user_id($value->user_id);
                        $customer = $value->apartment->bdcCustomers->where('type', 0)->first();
                        if ($receipt->type_payment == 'tien_mat') {
                            $status = 'PT';
                        } elseif ($receipt->type_payment == 'chuyen_khoan' || $receipt->type_payment == 'vi') {
                            $status = 'BC';
                        } else {
                            $status = 'VNPay';
                        }
                        $content[] = [
                            'Loại NK' =>   $status,
                            'Số chứng từ' =>  $receipt->receipt_code,
                            'Kỳ kế toán' =>  date('Ym', strtotime(@$receipt->create_date)),
                            'Ngày phát sinh' => @$receipt->create_date ? date('d/m/Y', strtotime(@$receipt->create_date)) : null,
                            'Diễn giải' => 'THU TIỀN' .'+' .$service->name . '+kỳ'. date('Ym', strtotime(@$receipt->create_date)) .'+căn hộ'. @$receipt->apartment->name .'+'. @$customer->pubUserProfile->display_name .'+dự án'.  @$receipt->building->name,
                            'Mã Khách hàng-NCC' => $status == 'BC' ? 'O00000000001' : @$receipt->apartment->code_customer,
                            'Mã Ngân hàng' =>  @$receipt->payment_info_ngan_hang->bank_account,
                            'Cty Con - NH cho DXMB vay' =>  '',
                            'Mã Phòng ban' =>  @$receipt->building->building_code_manage,
                            'Mã Nhân viên' =>  '',
                            'Mã phí' =>  '',
                            'Hợp đồng' => '' ,
                            'Sản phẩm' =>  @$receipt->apartment->code,
                            'Block' =>   @$receipt->apartment->buildingPlace->code,
                            'Dự án' =>  @$receipt->building->building_code_manage,
                            'Mã thu' =>   @$value->debitdetail->code_receipt,
                            'Khế ước' =>  "",
                            'CP không hợp lệ' =>  "",
                            'Mã tài khoản' =>  @$receipt->accounting_account_tai_khoan_no->code,
                            'TKDƯ' =>   @$receipt->accounting_account_tai_khoan_co->code,
                            'Số tiền' =>   $value->cost,
                            'Nợ/Có' =>  'D',
                            'Ký hiệu hóa đơn' =>  "",
                            'Số hóa đơn' =>  "",
                            'Ngày hóa đơn' =>  "",
                            'Loại thuế' => "",
                            'Thuế suất' =>  "",
                            'Tiền trước thuế' => "",
                            'Mẫu số hóa đơn' => "",
                            'Người nộp' =>  @$user->email,
                            'Người bán hàng' =>  "",
                            'Phiếu cấn trừ' =>  "",
                            'Mã phiếu eApprove' =>  "",
                            'Ghi chú' => $receipt->receipt_code,
                        ];
                        $content[] = [
                            'Loại NK' =>   $status,
                            'Số chứng từ' =>  $receipt->receipt_code,
                            'Kỳ kế toán' =>  date('Ym', strtotime(@$receipt->create_date)),
                            'Ngày phát sinh' => @$receipt->create_date ? date('d/m/Y', strtotime(@$receipt->create_date)) : null,
                            'Diễn giải' => 'THU TIỀN' .'+' .$service->name . '+kỳ'. date('Ym', strtotime(@$receipt->create_date)) .'+căn hộ'. @$receipt->apartment->name .'+'. @$customer->pubUserProfile->display_name .'+dự án'.  @$receipt->building->name,
                            'Mã Khách hàng-NCC' => $status == 'BC' ? 'O00000000001' : @$receipt->apartment->code_customer,
                            'Mã Ngân hàng' =>  @$receipt->payment_info_ngan_hang->bank_account,
                            'Cty Con - NH cho DXMB vay' =>  '',
                            'Mã Phòng ban' =>  @$receipt->building->building_code_manage,
                            'Mã Nhân viên' =>  '',
                            'Mã phí' =>  '',
                            'Hợp đồng' => '' ,
                            'Sản phẩm' =>  @$receipt->apartment->code,
                            'Block' =>   @$receipt->apartment->buildingPlace->code,
                            'Dự án' =>  @$receipt->building->building_code_manage,
                            'Mã thu' =>   @$value->debitdetail->code_receipt,
                            'Khế ước' =>  "",
                            'CP không hợp lệ' =>  "",
                            'Mã tài khoản' =>  @$receipt->accounting_account_tai_khoan_no->code,
                            'TKDƯ' =>   @$receipt->accounting_account_tai_khoan_co->code,
                            'Số tiền' =>   $value->cost,
                            'Nợ/Có' =>  'C',
                            'Ký hiệu hóa đơn' =>  "",
                            'Số hóa đơn' =>  "",
                            'Ngày hóa đơn' =>  "",
                            'Loại thuế' => "",
                            'Thuế suất' =>  "",
                            'Tiền trước thuế' => "",
                            'Mẫu số hóa đơn' => "",
                            'Người nộp' =>  @$user->email,
                            'Người bán hàng' =>  "",
                            'Phiếu cấn trừ' =>  "",
                            'Mã phiếu eApprove' =>  "",
                            'Ghi chú' => $receipt->receipt_code,
                        ];
                    }
                     // data of excel
                     if ($content) {
                        $sheet->fromArray($content);
                    }
                });
            })->store('xlsx',storage_path('exports/'));
$file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
return response()->download($file)->deleteFileAfterSend(true);
             
    }

    public function tien_thua(Request $request)
    {
        $data['meta_title'] = 'Quản lý tiền thừa';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['filter'] = $request->all();
        $data['tien_thua'] = PaymentDetail::where(['bdc_building_id'=>$this->building_active_id,'type_payment'=>PaymentDetail::tien_thua])
                                            ->where(function($query) use ($request){
                                                $query->whereHas('receipt',function($query) use ($request){
                                                        if(isset($request->keyword) && $request->keyword != null){
                                                            $query->where('customer_name','like','%'.$request->keyword.'%')
                                                                ->orwhere('receipt_code','like','%'.$request->keyword.'%');
                                                        }
                                                    })
                                                    ->orWhereHas('apartment',function($query) use ($request){
                                                        if(isset($request->keyword) && $request->keyword != null){
                                                            $query->where('name',$request->keyword)
                                                                    ->where('building_id',$this->building_active_id);
                                                        }
                                                    });
                                            })
                                            ->orderBy('created_at','desc')
                                            ->paginate($data['per_page']);
        return view('payment-detail.list_tien_thua', $data);
    }
    public function tien_thua_export(Request $request)
    {
        $tien_thua = PaymentDetail::where(['bdc_building_id'=>$this->building_active_id,'type_payment'=>PaymentDetail::tien_thua])
                                    ->where(function($query) use ($request){
                                        $query->whereHas('receipt',function($query) use ($request){
                                                if(isset($request->keyword) && $request->keyword != null){
                                                    $query->where('customer_name','like','%'.$request->keyword.'%')
                                                        ->orwhere('receipt_code','like','%'.$request->keyword.'%');
                                                }
                                            })
                                            ->orWhereHas('apartment',function($query) use ($request){
                                                if(isset($request->keyword) && $request->keyword != null){
                                                    $query->where('name',$request->keyword)
                                                            ->where('building_id',$this->building_active_id);
                                                }
                                            });
                                    })
                                    ->orderBy('created_at','desc')->get();
        $result = Excel::create('Quản lý tiền thừa', function ($excel) use ($tien_thua) {
            $excel->setTitle('danh sách tiền thừa');
            $excel->sheet('danh sách tiền thừa', function ($sheet) use ($tien_thua) {
                $row = 1;
                $sheet->row($row, [
                    'STT',
                    'Căn hộ',
                    'Tòa',
                    'Khách hàng',
                    'Mã phiếu thu',
                    'Tiền thừa',
                    'Ngày tạo',
                    'Người tạo'
                ]);
                foreach ($tien_thua as $key => $value) {
                    $row++;
                    $sheet->row($row, [
                        ($key + 1),
                        @$value->apartment->name,
                        @$value->apartment->code,
                        @$value->receipt->customer_name,
                        @$value->receipt->receipt_code,
                        $value->cost,
                        @$value->created_at,
                        @$value->user->email
                    ]);
                }
            });
        })->store('xlsx',storage_path('exports/'));
$file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
return response()->download($file)->deleteFileAfterSend(true);
             
    }
    public function hach_toan(Request $request)
    {
        $data['meta_title'] = 'Quản lý hạch toán tiền thừa';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['filter'] = $request->all();
        $data['hach_toan'] = PaymentDetail::where(['bdc_building_id'=>$this->building_active_id,'type_payment'=>PaymentDetail::hach_toan])
                                            ->where(function($query) use ($request){
                                                $query->whereHas('receipt',function($query) use ($request){
                                                        if(isset($request->keyword) && $request->keyword != null){
                                                            $query->where('customer_name','like','%'.$request->keyword.'%')
                                                                ->orwhere('receipt_code','like','%'.$request->keyword.'%');
                                                        }
                                                      })
                                                      ->orWhereHas('apartment',function($query) use ($request){
                                                        if(isset($request->keyword) && $request->keyword != null){
                                                            $query->where('name',$request->keyword)
                                                                    ->where('building_id',$this->building_active_id);
                                                        }
                                                     });
                                            })
                                            ->orderBy('created_at','desc')
                                            ->paginate($data['per_page']);
        return view('payment-detail.list_hach_toan', $data);
    }
    public function hach_toan_export(Request $request)
    {
        $hach_toan = PaymentDetail::where(['bdc_building_id'=>$this->building_active_id,'type_payment'=>PaymentDetail::hach_toan])
                                    ->where(function($query) use ($request){
                                        $query->whereHas('receipt',function($query) use ($request){
                                                if(isset($request->keyword) && $request->keyword != null){
                                                    $query->where('customer_name','like','%'.$request->keyword.'%')
                                                        ->orwhere('receipt_code','like','%'.$request->keyword.'%');
                                                }
                                            })
                                            ->orWhereHas('apartment',function($query) use ($request){
                                                if(isset($request->keyword) && $request->keyword != null){
                                                    $query->where('name',$request->keyword)
                                                            ->where('building_id',$this->building_active_id);
                                                }
                                            });
                                    })
                                    ->orderBy('created_at','desc')->get();
        $result = Excel::create('Quản lý hạch toán', function ($excel) use ($hach_toan) {
            $excel->setTitle('danh sách hạch toán');
            $excel->sheet('danh sách hạch toán', function ($sheet) use ($hach_toan) {
                $row = 1;
                $sheet->row($row, [
                    'STT',
                    'Mã phiếu thu',
                    'Số tiền',
                    'Công nợ dịch vụ',
                    'Kỳ',
                    'Căn hộ',
                    'Bảng kê',
                    'Ngày tạo',
                    'Người tạo'
                ]);
                foreach ($hach_toan as $key => $value) {
                    $row++;
                    $sheet->row($row, [
                        ($key + 1),
                        @$value->receipt->receipt_code,
                        $value->cost,
                        @$value->debitdetail->title,
                        @$value->debitdetail->cycle_name,
                        @$value->apartment->name,
                        @$value->debitdetail->bill->bill_code,
                        @$value->created_at,
                        @$value->user->email
                    ]);
                }
            });
        })->store('xlsx',storage_path('exports/'));
$file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
return response()->download($file)->deleteFileAfterSend(true);
             
    }
    public function tien_thua_show(Request $request)
    {
        $detail_receipt = AccountingVouches::where('bdc_receipt_id',$request->id)->get();
        if(!$detail_receipt){
            return $this->sendErrorApi();
        }
        $view = view("payment-detail._detail_receipt", [
            'detail_receipt' =>  $detail_receipt
        ])->render();
        return $this->responseSuccess([
            'html' => $view
        ]);
    }
}
