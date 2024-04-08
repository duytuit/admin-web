<?php

namespace App\Http\Controllers\AccountingVouches;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BuildingController;
use App\Models\BdcAccountingVouchers\AccountingVouches;
use App\Repositories\BdcV2PaymentDetail\PaymentDetailRepository;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AccountingVoucheController extends BuildingController
{
    use ApiResponse;

    public function __construct(
        Request $request
    ) {
        parent::__construct($request);
        $this->middleware('auth', ['except' => []]);
        //$this->middleware('route_permision');
        Carbon::setLocale('vi');
    }

    public function tien_thua(Request $request)
    {
        $data['meta_title'] = 'Quản lý tiền thừa';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['filter'] = $request->all();
        $data['tien_thua'] = AccountingVouches::where(['bdc_building_id'=>$this->building_active_id,'type_payment'=>AccountingVouches::tien_thua])
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
        return view('accounting-voucher.list_tien_thua', $data);
    }
    public function tien_thua_export(Request $request)
    {
        $tien_thua = AccountingVouches::where(['bdc_building_id'=>$this->building_active_id,'type_payment'=>AccountingVouches::tien_thua])
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
                        $value->cost_paid,
                        @$value->created_at,
                        @$value->user->email
                    ]);
                }
            });
        })->store('xlsx', storage_path('exports/'));
        $file     = storage_path('exports/' . $result->filename . '.' . $result->ext);
        return response()->download($file)->deleteFileAfterSend(true);
             
    }
    public function hach_toan(Request $request)
    {
        $data['meta_title'] = 'Quản lý hạch toán tiền thừa';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['filter'] = $request->all();
        $data['hach_toan'] = AccountingVouches::where(['bdc_building_id'=>$this->building_active_id,'type_payment'=>AccountingVouches::hach_toan])
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
        return view('accounting-voucher.list_hach_toan', $data);
    }
    public function hach_toan_export(Request $request)
    {
        $hach_toan = AccountingVouches::where(['bdc_building_id'=>$this->building_active_id,'type_payment'=>AccountingVouches::hach_toan])
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
                        $value->cost_paid,
                        @$value->debitdetail->title,
                        @$value->debitdetail->cycle_name,
                        @$value->apartment->name,
                        @$value->debitdetail->bill->bill_code,
                        @$value->created_at,
                        @$value->user->email
                    ]);
                }
            });
        })->store('xlsx', storage_path('exports/'));
        $file     = storage_path('exports/' . $result->filename . '.' . $result->ext);
        return response()->download($file)->deleteFileAfterSend(true);
             
    }
    public function tien_thua_show(Request $request)
    {
        $detail_receipt = PaymentDetailRepository::getDataByReceiptId($request->id);
        if(!$detail_receipt){
            return $this->sendErrorApi();
        }
        $view = view("accounting-voucher._detail_receipt", [
            'detail_receipt' =>  $detail_receipt
        ])->render();
        return $this->responseSuccess([
            'html' => $view
        ]);
    }
}
