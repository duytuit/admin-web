<?php

namespace App\Http\Controllers\BdcProvisionalReceipt\Api;

use App\Http\Controllers\BuildingController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProvisionalReceipt\ProvisionalReceiptRequest;
use App\Models\Apartments\Apartments;
use App\Models\Apartments\V2\UserApartments;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\BdcApartmentServicePrice\ApartmentServicePriceRepository;
use App\Repositories\BdcDebitDetail\DebitDetailRepository;
use App\Repositories\BdcProvisionalReceipt\ProvisionalReceiptRepository;
use App\Repositories\Config\ConfigRepository;
use App\Repositories\Customers\CustomersRespository;
use App\Traits\ApiResponse;
use Carbon\Carbon;

class ProvisionalReceiptController extends BuildingController
{
    use ApiResponse;

    protected $model;

    public function __construct(Request $request, ProvisionalReceiptRepository $provisionalReceiptRepository)
    {
        //$this->middleware('auth', ['except'=>[]]);
        //$this->middleware('route_permision');
        $this->model = $provisionalReceiptRepository;
        Carbon::setLocale('vi');
        parent::__construct($request);
    }

    public function filterApartmentId(
        Request $request, 
        CustomersRespository $customer, 
        DebitDetailRepository $debitDetailRepository,
        ApartmentServicePriceRepository $apartmentServicePriceRepository,
        $apartment_id)
    {
        $building_id = $this->building_active_id;
        $provisionalReceipts = $this->model->filterApartmentId($apartment_id);
        $provisionalReceipt = $provisionalReceipts->first();
        // lấy chủ hộ của căn hộ
        $_customer = UserApartments::getPurchaser($apartment_id, 0);
        if(!$_customer){
            return $this->responseError('Không tìm thấy chủ căn hộ.', 404);
        }
        $customerInfo = $_customer->user_info_first;
        $customer_name = $customerInfo->full_name;
        $customer_address = $customerInfo->address;

        switch($provisionalReceipt->payment_type){
            case 'tien_mat':
                $paymentType = "Tiền mặt";
                break;
            case 'chuyen_khoan':
                $paymentType = "Chuyển khoản";
                break;
            case 'vnpay':
                $paymentType = "VNPay";
                break;
            default: 
                $paymentType = "Chưa xác định";
                break;
        }

        $debitDetails = $debitDetailRepository->findMaxVersionWithNewSumeryDiffZero($building_id, $apartment_id, 0, null, null);
        $serviceIdList = array_column($debitDetails, 'bdc_service_id');
        $apartmentServicePrices = $apartmentServicePriceRepository->filterServiceIds($serviceIdList, $building_id, $apartment_id);

        // $view = view("receipt._receipt_previous", [
        //     'apartmentServicePrices' => $apartmentServicePrices,
        // ])->render();

        return $this->responseSuccess([
            // 'html' => $view,
            'provisionalReceipts' => $provisionalReceipts,
            
            'customer_name' => $customer_name,
            'customer_address' => $customer_address,
            'customer_name_provisional_receipt' => $provisionalReceipt->name,
            'price_provisional_receipt' => $provisionalReceipt->price,
            'description_provisional_receipt' => $provisionalReceipt->description,
            'payment_type_provisional_receipt' => $paymentType,
        ]);
    }
}
