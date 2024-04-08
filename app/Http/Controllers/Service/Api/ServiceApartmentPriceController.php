<?php

namespace App\Http\Controllers\Service\Api;

use App\Http\Controllers\BuildingController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Receipt\ReceiptRequest;
use App\Models\BdcV2DebitDetail\DebitDetail;
use App\Models\PublicUser\Users;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Validator;
use App\Models\PublicUser\UserInfo;
use App\Repositories\BdcApartmentServicePrice\ApartmentServicePriceRepository;
use App\Repositories\BdcBills\BillRepository;
use App\Repositories\BdcDebitDetail\DebitDetailRepository;
use App\Repositories\BdcReceipts\ReceiptRepository;
use App\Repositories\Config\ConfigRepository;
use App\Repositories\Customers\CustomersRespository;
use App\Traits\ApiResponse;
use App\Services\AppConfig;
use Illuminate\Support\Carbon;
use App\Repositories\Service\ServiceRepository;
use Barryvdh\DomPDF\Facade as PDF;

class ServiceApartmentPriceController extends BuildingController
{
    use ApiResponse;

    private $model;
    private $serviceRepo;

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct(Request $request, ApartmentServicePriceRepository $model, ServiceRepository $serviceRepo)
    {
        ////$this->middleware('jwt.auth');
        ////$this->middleware('route_permision');
        $this->serviceRepo = $serviceRepo;
        Carbon::setLocale('vi');
        $this->model = $model;
        parent::__construct($request);
    }

    public function filterById($id)
    {
        $aparmentServicePrice = $this->model->findById($id);
        $last_time_pay = $aparmentServicePrice->last_time_pay;
        $aparmentServicePrice->last_time_pay = date('d-m-Y',strtotime($aparmentServicePrice->last_time_pay));
        $aparmentServicePrice->ngay_chot = @$aparmentServicePrice->service->bill_date;
        $debit = DebitDetail::where(['bdc_apartment_service_price_id'=>$aparmentServicePrice->id,'to_date'=>$last_time_pay])->first();
        $aparmentServicePrice->cycle_name = $debit ? $debit->cycle_name : null; 
        return $this->responseSuccess([
            'aparmentServicePrice' => $aparmentServicePrice,
        ]);
    }
    public function filterByApartment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bdc_apartment_id'      => 'required'
          ]);
    
        if ($validator->fails()) {
            return $this->sendErrorResponse($validator->errors());
        }
        $aparmentServicePrice = $this->serviceRepo->findByApartment($request);
        if($aparmentServicePrice){
            return $this->sendSuccessApi($aparmentServicePrice);
        }
        return $this->sendErrorResponse('Không có dữ liệu');
    }
}
