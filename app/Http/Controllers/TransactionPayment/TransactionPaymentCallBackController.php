<?php

namespace App\Http\Controllers\TransactionPayment;

use App\Commons\Helper;
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
use App\Models\Campain;
use App\Models\SentStatus;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Repositories\TransactionPayment\TransactionPaymentRepository;
use App\Services\SendSMSSoapV2;
use App\Services\ServiceSendMailV2;
use Illuminate\Pagination\LengthAwarePaginator;

class TransactionPaymentCallBackController extends Controller
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
    protected $_publicUsersProfileRespository;


    public function __construct(Request $request,TransactionPaymentRepository $transactionPaymentRepository, PublicUsersProfileRespository $publicUsersProfileRespository)
    {
        $this->_transactionPaymentRepository = $transactionPaymentRepository;
        $this->_publicUsersProfileRespository = $publicUsersProfileRespository;
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
        $data['transactionPayment'] = TransactionPayment::whereNull('bdc_receipt_id')->orderbyDesc('created_at')->paginate($data['per_page']);
        return view('transaction-payment.tabs.list_transaction_payment', $data);
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
         $data['per_page'] = $perPage;
 
         // Start displaying items from this number;
         $offSet = ($page * $perPage) - $perPage; // Start displaying items from this number
         $transactionPaymentReceipt = $this->_transactionPaymentRepository->transactionPaymentReceipt(67, $request);
         $itemsForCurrentPage = array_slice($transactionPaymentReceipt, $offSet, $perPage, true);
 
         $data['transactionPaymentReceipt'] = new LengthAwarePaginator($itemsForCurrentPage, count($transactionPaymentReceipt), $perPage, $page, ['path' => route('admin.transactionpayment.service_detail_payment')]);

         return view('transaction-payment.tabs.list_service_detail_payment', $data);
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
            $virtualAccountPayment = VirtualAccountPayment::where('virtual_acc_id',$request->virtual_account)->first();
            $pub_user_profile = $this->_publicUsersProfileRespository->findByPubUserIdByDeadApartment($virtualAccountPayment->bdc_building_id,$virtualAccountPayment->bdc_apartment_id);
            $transactionPayment = TransactionPayment::create([
                'bdc_apartment_id' => @$virtualAccountPayment->bdc_apartment_id,
                'master_account' => $request->master,
                'virtual_acc' => $request->virtual_account,
                'payer_name' => $request->customer,
                'amount' => $request->amount,
                'description' => $request->note,
                'trans_id' => $request->trans_id,
                'trans_record_time' => $request->record_time ?? Carbon::now(),
                'trans_excution_time' => $request->excution_time ?? Carbon::now(),
                'bank_payment' => $request->bank_payment,
            ]);
            if($pub_user_profile){
                $total = ['email'=>1, 'app'=> 0, 'sms'=> 0];
                $campain = Campain::updateOrCreateCampain("Gửi mail cho: ".@$pub_user_profile->email, config('typeCampain.NOTIFY_TRANSACTION_PAYMENT'), null, $total, (int)$pub_user_profile->bdc_building_id, 0, 0);
        
                 
                ServiceSendMailV2::setItemForQueue( [
                    'params' => [
                        '@ten_khach_hang' => @$pub_user_profile->display_name,
                        '@ten_can_ho' => @$virtualAccountPayment->bdcApartment->name,
                        '@tienchuyenkhoan' => $request->amount
                    ],
                    'cc' => @$pub_user_profile->email,
                    'building_id' => (int)$pub_user_profile->bdc_building_id,
                    'type' => ServiceSendMailV2::NOTIFY_TRANSACTION_PAYMENT,
                    'status' => 'prepare',
                    'subject' => '[BuildingCare] thông báo xác nhận đến khách hàng',
                    'content'=> $request->note,
                    'campain_id' => $campain->id
                ]);

            }

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
