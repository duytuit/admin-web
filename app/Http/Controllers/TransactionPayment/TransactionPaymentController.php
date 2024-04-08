<?php

namespace App\Http\Controllers\TransactionPayment;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\TransactionPayment\TransactionPayment;
use App\Models\VirtualAccountPayment\VirtualAccountPayment;
use Illuminate\Support\Carbon;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\TransactionPayment\TransactionPaymentRequest;
use App\Repositories\TransactionPayment\TransactionPaymentRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Controllers\BuildingController;
use Maatwebsite\Excel\Facades\Excel;

class TransactionPaymentController extends BuildingController
{

    const status_reject = 2;
    const status_success = 1;
    use ApiResponse;
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private $model;
    private $modelApartment;
    protected $_transactionPaymentRepository;


    public function __construct(Request $request,TransactionPaymentRepository $transactionPaymentRepository)
    {
        $this->_transactionPaymentRepository = $transactionPaymentRepository;
        parent::__construct($request);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data['meta_title'] = 'Quản lý giao dịch banking';
        $data['per_page'] = Cookie::get('per_page',10);
        $data['filter'] = $request->all();
        $data['transactionPayment'] = $this->getAllTransactionPayment($request)->paginate($data['per_page']);
        return view('transaction-payment.tabs.list_transaction_payment', $data);
    }
    private function getAllTransactionPayment(Request $request){
                     return TransactionPayment::whereNull('bdc_receipt_id')
                                                ->where(function($query) use($request){
                                                    if(isset($request->keyword) && $request->keyword !=null){
                                                        $query->where('virtual_acc',$request->keyword);
                                                    }
                                                    if(isset($request->from_date) && $request->from_date !=null){
                                                        $from_date = Carbon::parse($request->from_date)->format('Y-m-d');
                                                        $query->whereDate('created_at','>=',$from_date);
                                                    }
                                                    if(isset($request->to_date) && $request->to_date !=null){
                                                        $to_date = Carbon::parse($request->to_date)->format('Y-m-d');
                                                        $query->whereDate('created_at','<=',$to_date);
                                                    }
                                                    if(isset($request->status) && $request->status !=null){
                                                        $query->where('status',$request->status);
                                                    }
                                                    if(isset($request->bdc_apartment_id) && $request->bdc_apartment_id !=null){
                                                        $query->where('bdc_apartment_id',$request->bdc_apartment_id);
                                                    }
                                                })
                                                ->whereHas('bdcApartment',function($query) use ($request){
                                                    $query->where('building_id',$this->building_active_id);
                                                })
                                                ->orderbyDesc('created_at');
    }
    public function exportTransactionPayment(Request $request){

            $result = $this->getAllTransactionPayment($request)->get();

            $result = Excel::create('Báo cáo giao dịch banking', function ($excel) use ($result) {

                $excel->setTitle('Báo cáo giao dịch banking');
                $excel->sheet('Báo cáo', function ($sheet) use ($result) {
                    $row = 1;
                    $sheet->row($row, [
                        'STT',
                        'Mã giao dịch',
                        'Khách hàng',
                        'Căn hộ',
                        'Số tài khoản',
                        'Nội dung chuyển khoản',
                        'Số tiền',
                        'Ngày chuyển tiền',
                        'Trạng thái',
                        'Lý do',
                        'Người xác nhận',
                        'Ngày xác nhận',
                    ]);

                    foreach ($result as $key => $value) {
                        $row++;
                        if ($value->status == 0)
                            $status = 'chờ xác nhận';
                        elseif ($value->status == 1)
                            $status = 'đã duyệt';
                        else
                            $status = 'từ chối';    
                        $sheet->row($row, [
                            ($key+1),
                            @$value->trans_id,
                            @$value->payer_name,
                            @$value->bdcApartment->name,
                            $value->virtual_acc,
                            @$value->description,
                            @$value->amount,
                            date_format($value->created_at,'d/m/Y'),
                            $status,
                            $value->note,
                            @$value->User->email,
                            $value->user_id ? date_format($value->updated_at,'d/m/Y') : '',
                        ]);
                    }
                });
            })->store('xlsx',storage_path('exports/'));
$file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
return response()->download($file)->deleteFileAfterSend(true);
             
    }

     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function service_detail_payment(Request $request)
    {
        $data['meta_title'] = 'Khoản hoạch toán phiếu thu Banking';
         // Get the current page from the url if it's not set default to 1
         $page = $request->page ? $request->page : 1;

         // Number of items per page
         $perPage = Cookie::get('per_page', 10);
         $data['filter'] = $request->all();
         $data['per_page'] = $perPage;
 
         // Start displaying items from this number;
         $offSet = ($page * $perPage) - $perPage; // Start displaying items from this number
         $transactionPaymentReceipt = $this->_transactionPaymentRepository->transactionPaymentReceipt($this->building_active_id, $request);
         $itemsForCurrentPage = array_slice($transactionPaymentReceipt, $offSet, $perPage, true);
 
         $data['transactionPaymentReceipt'] = new LengthAwarePaginator($itemsForCurrentPage, count($transactionPaymentReceipt), $perPage, $page, ['path' => route('admin.transactionpayment.service_detail_payment')]);

         return view('transaction-payment.tabs.list_service_detail_payment', $data);
    }

    public function exportTransactionPaymentByServiceReceipt(Request $request){

            $result = $this->_transactionPaymentRepository->transactionPaymentReceipt($this->building_active_id, $request);

            $result = Excel::create('Báo cáo giao dịch banking', function ($excel) use ($result) {

                $excel->setTitle('Báo cáo giao dịch banking');
                $excel->sheet('Báo cáo', function ($sheet) use ($result) {
                    $row = 1;
                    $sheet->row($row, [
                        'STT',
                        'Căn hộ',
                        'Chủ hộ',
                        'Số điện thoại',
                        'Số tài khoản',
                        'Tổng ví tiền',
                        'Tổng thanh toán',
                        'Còn lại'
                    ]);

                    foreach ($result as $key => $value) {
                        $row++;    
                        $thanh_toan = (int)$value->chi_tien - (int)$value->hoan_tien;
                        $sheet->row($row, [
                            ($key+1),
                            @$value->name,
                            @$value->virtual_acc_name,
                            @$value->virtual_acc_mobile,
                            @$value->virtual_acc_id,
                            @number_format((int)$value->dong_tien),
                            @number_format($thanh_toan),
                            @number_format((int)$value->so_du)
                        ]);
                    }
                });
            })->store('xlsx',storage_path('exports/'));
$file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
return response()->download($file)->deleteFileAfterSend(true);
             
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
        Log::channel('transaction_payment')->info($request);
        try {
            DB::beginTransaction();
            $virtualAccountPayment = VirtualAccountPayment::where('virtual_acc_id',$request->virtual_acc)->first();
            $transactionPayment = TransactionPayment::create([
                'bdc_apartment_id' => @$virtualAccountPayment->bdc_apartment_id,
                'master_account' => $request->master_account,
                'virtual_acc' => $request->virtual_acc,
                'payer_name' => $request->payer_name,
                'amount' => $request->amount,
                'description' => $request->description,
                'trans_id' => $request->trans_id,
                'trans_record_time' => $request->trans_record_time ?? Carbon::now(),
                'trans_excution_time' => $request->trans_excution_time ?? Carbon::now(),
                'bank_payment' => $request->bank_payment,
            ]);
            
            DB::commit();
            return $this->sendSuccess_Api([],'Tạo giao dịch thành công.');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::channel('transaction_payment')->error($th->getMessage());
            return $this->sendError($th->getMessage());
        }
       
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeReceipt(Request $request)
    {
        try {
            DB::beginTransaction();
            $transactionPayment = TransactionPayment::create([
                'bdc_apartment_id' => $request->bdc_apartment_id,
                'bdc_receipt_id' => $request->bdc_receipt_id,
                'amount' => $request->amount,
                'note' => $request->note,
                'status' => self::status_reject
            ]);
            
            DB::commit();
            return $this->sendSuccess_Api([],'Tạo giao dịch thành công.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->sendError($th->getMessage());
        }
       
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
    public function status_confirm_reject(TransactionPaymentRequest $request)
    {
        try {
            $transactionPayment = TransactionPayment::find($request->id);
            if($transactionPayment){
                $transactionPayment->update([
                    'user_id' => auth()->user()->id,
                    'note'  => $request->note,
                    'status' => self::status_reject, // từ chối
                ]);
                return $this->sendSuccess_Api([],'xác nhận thành công.');
            }
            return $this->sendError([],'xác nhận thất bại.');
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function status_confirm_success(Request $request)
    {
        try {
            $transactionPayment = TransactionPayment::where('id',$request->id)->first();
            if($transactionPayment){
                $transactionPayment->update([
                    'user_id' => auth()->user()->id,
                    'status' => self::status_success, // duyệt
                ]);
                return redirect()->route('admin.transactionpayment.index')->with('success', 'xác nhận thành công!');
            }
            return redirect()->route('admin.transactionpayment.index')->with('error', 'xác nhận thất bại!');
        } catch (\Throwable $th) {
            return redirect()->route('admin.transactionpayment.index')->with('error', 'xác nhận thất bại!');
        }
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
}
