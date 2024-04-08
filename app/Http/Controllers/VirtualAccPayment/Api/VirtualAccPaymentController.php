<?php

namespace App\Http\Controllers\VirtualAccPayment\Api;

use App\Commons\Helper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BuildingController;
use App\Models\TransactionPayment\TransactionPayment;
use App\Models\VirtualAccountPayment\VirtualAccountPayment;
use App\Repositories\Customers\CustomersRespository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\TransactionPayment\TransactionPaymentRepository;


class VirtualAccPaymentController extends BuildingController
{
    protected $_client;
    protected $_publicUsersProfileRespository;
    protected $_transactionPaymentRepository;
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    use ApiResponse;

    public function __construct(Request $request, PublicUsersProfileRespository $publicUsersProfileRespository, TransactionPaymentRepository $transactionPaymentRepository)
    {
        //$this->middleware('jwt.auth');
        Carbon::setLocale('vi');
        $_auth = [
            env('CLIENT_BANK'),
            env('CLIENT_SECRET_BANK')
        ];
        $this->_client = new \GuzzleHttp\Client(['auth' => $_auth]);
        $this->_publicUsersProfileRespository = $publicUsersProfileRespository;
        $this->_transactionPaymentRepository = $transactionPaymentRepository;
        parent::__construct($request);
    }
    public function store(Request $request){
        Log::channel('virtual_acc_payment')->info($request);
        try {
            DB::beginTransaction();

            $pub_user_profile = $this->_publicUsersProfileRespository->findByPubUserIdByDeadApartment($request->bdc_building_id,$request->bdc_apartment_id);

            if($pub_user_profile){
                $virtualAccountPayment = VirtualAccountPayment::where([
                    'bdc_building_id' => $request->bdc_building_id,
                    'bdc_apartment_id' => $request->bdc_apartment_id,
                    'virtual_alt_key' => $pub_user_profile->id
                ])->first();
            }

            if(isset($virtualAccountPayment)){
                $get_expiry_date =  Carbon::parse($virtualAccountPayment->expiry_date);
                $get_current_date = Carbon::now();
                if($get_current_date >= $get_expiry_date){
                        $virtualAccountPayment->value_date = Carbon::now();
                        $virtualAccountPayment->expiry_date = Carbon::now()->addYear(1);
                        $virtualAccountPayment->save();
                        DB::commit();
                    $create_virtualAccountPayment = [
                        'b_id'  =>$request->bdc_building_id,
                        'virtualAccName'=> Helper::convert_vi_to_en($virtualAccountPayment->virtual_acc_name) ?? null,
                        'virtualMobile'=> $virtualAccountPayment->virtual_acc_mobile,
                        'virtualAltKey'=> $virtualAccountPayment->virtual_alt_key,
                        'expiryDate'=> Carbon::now()->addYear(1)->format('Y-m-d')
                        ];   
                    // cập nhật lại expiry_date gửi về servive vpbank
                    $result_acc_payment_vpbank = \GuzzleHttp\json_decode($this->_client->request('POST',env('DOMAIN_BANK').'v2/vpbank/create-virtual-account',[
                        'json' => $create_virtualAccountPayment
                    ])->getBody()->getContents());

                    if(isset($result_acc_payment_vpbank) && $result_acc_payment_vpbank->success == true){
                        $responseData =[
                              'virtual_acc_id' => $result_acc_payment_vpbank->data->virtual_acc_id,
                              'virtual_acc_name' => $virtualAccountPayment->virtual_acc_name,
                              'content' => 'CAN HO '.@$virtualAccountPayment->bdcApartment->name.' THANH TOAN CONG NO',
                              'expiry_date' => $result_acc_payment_vpbank->data->expiry_date,
                        ];
                        return $this->sendResponse($responseData,'Lấy thông tin thành công.');
                    }
                    return response()->json($result_acc_payment_vpbank);
                }
                $responseData =[
                    'virtual_acc_id' => $virtualAccountPayment->virtual_acc_id,
                    'virtual_acc_name' => $virtualAccountPayment->virtual_acc_name,
                    'content' => 'CAN HO '.@$virtualAccountPayment->bdcApartment->name.' THANH TOAN CONG NO',
                    'expiryDate' => $virtualAccountPayment->expiry_date,
                ];
                return $this->sendResponse($responseData,'Lấy thông tin thành công.');
            }else{
                 $customer_apartment = $this->_publicUsersProfileRespository->findByPubUserIdByDeadApartment($request->bdc_building_id,$request->bdc_apartment_id);

                 if($customer_apartment){
                    $create_virtualAccountPayment = [
                        'b_id'  =>$request->bdc_building_id,
                        'virtualAccName'=> Helper::convert_vi_to_en($customer_apartment->display_name) ?? null,
                        'virtualMobile'=> $customer_apartment->phone,
                        'virtualAltKey'=> $customer_apartment->id,
                        'expiryDate'=> Carbon::now()->addYear(1)->format('Y-m-d')
                     ];
                     $result_InfoBank = \GuzzleHttp\json_decode($this->_client->request('GET',env('DOMAIN_BANK').'v2/vpbank/list?b_id='.$request->bdc_building_id)->getBody()->getContents());
                     if(isset($result_InfoBank) && $result_InfoBank->success == true){
                         $result_payment_vpbank = !empty($result_InfoBank->data) ? $result_InfoBank->data[0] : null;
                     }
                    // cập nhật lại expiry_date gửi về servive vpbank
                    $result_acc_payment_vpbank = \GuzzleHttp\json_decode($this->_client->request('POST',env('DOMAIN_BANK').'v2/vpbank/create-virtual-account',[
                        'json' => $create_virtualAccountPayment
                    ])->getBody()->getContents());
                    
                    $virtual_acc_name = isset($result_payment_vpbank->service_name) ? $result_payment_vpbank->service_name.Helper::convert_vi_to_en($result_acc_payment_vpbank->data->virtual_acc_name) : Helper::convert_vi_to_en($result_acc_payment_vpbank->data->virtual_acc_name);

                    if(isset($result_acc_payment_vpbank) && $result_acc_payment_vpbank->success == true){
                        $virtualAccountPayment_create = VirtualAccountPayment::create([
                            'bdc_building_id' => $result_acc_payment_vpbank->data->b_id,
                            'bdc_apartment_id' => $request->bdc_apartment_id,
                            'virtual_acc_id' => $result_acc_payment_vpbank->data->virtual_acc_id,
                            'virtual_acc_no' => $result_acc_payment_vpbank->data->virtual_acc_no,
                            'virtual_acc_name' => $virtual_acc_name,
                            'virtual_acc_mobile' => $result_acc_payment_vpbank->data->virtual_acc_mobile,
                            'virtual_alt_key' => $result_acc_payment_vpbank->data->virtual_alt_key,
                            //'status' => $result_acc_payment_vpbank->data->status == 'ACTIVE' ? 1 : 0,
                            'open_date' => $result_acc_payment_vpbank->data->open_date,
                            'value_date' => $result_acc_payment_vpbank->data->value_date,
                            'expiry_date' => $result_acc_payment_vpbank->data->expiry_date,
                       ]);
                       DB::commit();
                       $responseData =[
                            'virtual_acc_id' => $result_acc_payment_vpbank->data->virtual_acc_id,
                            'virtual_acc_name' => $virtualAccountPayment_create->virtual_acc_name,
                            'content' => 'CAN HO '.@$virtualAccountPayment_create->bdcApartment->name.' THANH TOAN CONG NO',
                            'expiry_date' => $result_acc_payment_vpbank->data->expiry_date
                        ];
                        return $this->sendResponse($responseData,'Lấy thông tin thành công.');
                    }
                    return response()->json($result_acc_payment_vpbank);
                 }else{
                    return $this->sendResponse([],'Căn hộ không có chủ hộ.');
                 }
               
            }
            $responseData = [
                'success' => false,
                'message' => 'không thành công!'
            ];
            return response()->json($responseData);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::channel('virtual_acc_payment')->error($th->getMessage());
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
                'status' => 2
            ]);
            
            DB::commit();
            return $this->sendSuccess_Api([],'Tạo phiếu thu thành công.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->sendError($th->getMessage());
        }
       
    }

     /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getTransactionReceipt(Request $request)
    {
        try {
            $transactionReceipt = $this->_transactionPaymentRepository->transactionPaymentReceipt($request->bdc_building_id,$request->bdc_apartment_id);
            return $this->sendSuccess_Api($transactionReceipt,'Tạo phiếu thu thành công.');
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage());
        }
       
    }
}
