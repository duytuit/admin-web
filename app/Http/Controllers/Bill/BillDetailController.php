<?php

namespace App\Http\Controllers\Bill;

use App\Http\Controllers\BuildingController;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\BdcBills\BillRepository;
use App\Repositories\BdcDebitDetail\DebitDetailRepository;
use App\Repositories\Building\BuildingRepository;
use App\Repositories\Config\ConfigRepository;
use App\Repositories\Customers\CustomersRespository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Services\ServiceSendMail;
use App\Services\ServiceSendMailV2;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use App\Repositories\Building\BuildingPlaceRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Controllers\Controller;
use App\Models\Apartments\Apartments;
use App\Models\BdcV2DebitDetail\DebitDetail;
use App\Models\Building\Building;
use App\Models\Building\BuildingPlace;
use App\Models\PaymentInfo\PaymentInfo;
use App\Models\PublicUser\UserInfo;
use App\Models\PublicUser\Users;
use App\Repositories\BdcV2DebitDetail\DebitDetailRepository as BdcV2DebitDetailDebitDetailRepository;

class BillDetailController extends Controller
{
    public $billRepo;
    public $debitDetailRepo;
    public $buildingRepo;
    public $apartmentRepo;
    public $customerRepo;
    public $profileRepo;
    private $modelBuildingPlace;

    public function __construct(
        Request $request,
        BillRepository $billRepo,
        DebitDetailRepository $debitDetailRepo,
        BuildingRepository $buildingRepo,
        ApartmentsRespository $apartmentRepo,
        CustomersRespository $customerRepo,
        PublicUsersProfileRespository $profileRepo,
        BuildingPlaceRepository $modelBuildingPlace
    ) {
        $this->billRepo = $billRepo;
        $this->debitDetailRepo = $debitDetailRepo;
        $this->buildingRepo = $buildingRepo;
        $this->apartmentRepo = $apartmentRepo;
        $this->customerRepo = $customerRepo;
        $this->profileRepo = $profileRepo;
        $this->modelBuildingPlace = $modelBuildingPlace;
    }

    public function index(Request $request, BillRepository $billRepository, ConfigRepository $configRepository, DebitDetailRepository $debitRepo, $billcode)
    {
        $data['meta_title'] = 'chi tiết hóa đơn';

        $bill = $this->billRepo->findBillCode_v1($billcode);
        if(!$bill){
            return response('Không tìm thấy hóa đơn hoặc hóa đơn đã bị xóa.');
        }
        $building = Building::get_detail_building_by_building_id($bill->bdc_building_id);
        $building_payment_info = PaymentInfo::where('bdc_building_id',$bill->bdc_building_id)->orderBy('updated_at','desc')->first();
        $building->manager_building = UserInfo::where('pub_user_id',$building->manager_id)->where('type',Users::USER_WEB)->first();
        $user = auth()->user();
        if($bill || @$user){
            $apartment = Apartments::get_detail_apartment_by_apartment_id($bill->bdc_apartment_id);
            if ($building && @$building->config_menu == 1) { // kế toán v1
                $debit_detail = $this->debitDetailRepo->getDetailBillId($bill->id);
                $totalPaymentDebit = $this->debitDetailRepo->findMaxVersionWithBuildingApartment(@$bill->building->id, $bill->bdc_apartment_id, $bill->id);
                $total_paid = $this->debitDetailRepo->findMaxVersionPaid_v2(@$bill->building->id, $bill->bdc_apartment_id, $bill->id);
                if (@$request->version == 2) {

                    $debit_detail = BdcV2DebitDetailDebitDetailRepository::getDetailBillId($bill->id);

                    $configPdfBill = $configRepository->findByKeyActiveFirst($building->id, $configRepository::BANGKE_PDF);
                    $data_bill = null;
                    if($configPdfBill) {
                        $data_bill[0]['building_payment_info'] = $building_payment_info;
                        $data_bill[0]['building'] = $building;
                        $data_bill[0]['debit_detail'] = $debit_detail;
                        $data_bill[0]['apartment'] = $apartment;
                        $data_bill[0]['bill'] = $bill;
                        $data_bill[0]['buildingPlace'] = BuildingPlace::get_detail_bulding_place_by_bulding_place_id($apartment->building_place_id);
                        return view('bill.'.$configPdfBill->value,compact('data_bill'));
                    }else{
                        $data_bill[0]['building_payment_info'] = $building_payment_info;
                        $data_bill[0]['building'] = $building;
                        $data_bill[0]['debit_detail'] = $debit_detail;
                        $data_bill[0]['apartment'] = $apartment;
                        $data_bill[0]['bill'] = $bill;
                        $data_bill[0]['buildingPlace'] = BuildingPlace::get_detail_bulding_place_by_bulding_place_id($apartment->building_place_id);
                        return view('bill.detail_bill_mau1', compact('data_bill'));
                    }
                }
                return view('bill.detail_bill_mau1_old', compact('debit_detail', 'building', 'apartment', 'bill', 'total_paid', 'totalPaymentDebit','building_payment_info'));
            }  
            if ($building && @$building->config_menu == 2 && @$building->id != 17) { // kế toán v2
              // dd(2343);
                $totalPaymentDebit = $this->debitDetailRepo->findMaxVersionWithBuildingApartment(@$bill->building->id, $bill->bdc_apartment_id, $bill->id);
                $total_paid = $this->debitDetailRepo->findMaxVersionPaid_v2(@$bill->building->id, $bill->bdc_apartment_id, $bill->id);
                $debit_detail = BdcV2DebitDetailDebitDetailRepository::getDetailBillId($bill->id);
                $configPdfBill = $configRepository->findByKeyActiveFirst($building->id, $configRepository::BANGKE_PDF);
                // dd($configPdfBill);
                $data_bill = null;
                if($configPdfBill) {
                    $data_bill[0]['building_payment_info'] = $building_payment_info;
                    $data_bill[0]['building'] = $building;
                    $data_bill[0]['debit_detail'] = $debit_detail;
                    $data_bill[0]['apartment'] = $apartment;
                    $data_bill[0]['bill'] = $bill;
                    $data_bill[0]['buildingPlace'] = BuildingPlace::get_detail_bulding_place_by_bulding_place_id($apartment->building_place_id);
                    return view('bill.'.$configPdfBill->value,compact('data_bill'));
                }else{
                    $data_bill[0]['building_payment_info'] = $building_payment_info;
                    $data_bill[0]['building'] = $building;
                    $data_bill[0]['debit_detail'] = $debit_detail;
                    $data_bill[0]['apartment'] = $apartment;
                    $data_bill[0]['bill'] = $bill;
                    $data_bill[0]['buildingPlace'] = BuildingPlace::get_detail_bulding_place_by_bulding_place_id($apartment->building_place_id);
                    return view('bill.detail_bill_mau1', compact('data_bill'));
                }
               
            }
            if ($building && @$building->config_menu == 2 && @$building->id == 17) { // kế toán v2 áp dụng tòa 17
                $debit_detail = $this->debitDetailRepo->getDetailBillId($bill->id);
                $totalPaymentDebit = $this->debitDetailRepo->findMaxVersionWithBuildingApartment(@$bill->building->id, $bill->bdc_apartment_id, $bill->id);
                $total_paid = $this->debitDetailRepo->findMaxVersionPaid_v2(@$bill->building->id, $bill->bdc_apartment_id, $bill->id);
                    $debit_detail = BdcV2DebitDetailDebitDetailRepository::getDetailBillId($bill->id);
                    $configPdfBill = $configRepository->findByKeyActiveFirst($building->id, $configRepository::BANGKE_PDF);
                    $data_bill = null;
                    if($configPdfBill) {
                        $data_bill[0]['building_payment_info'] = $building_payment_info;
                        $data_bill[0]['building'] = $building;
                        $data_bill[0]['debit_detail'] = $debit_detail;
                        $data_bill[0]['apartment'] = $apartment;
                        $data_bill[0]['bill'] = $bill;
                        $data_bill[0]['buildingPlace'] = BuildingPlace::get_detail_bulding_place_by_bulding_place_id($apartment->building_place_id);
                        return view('bill.'.$configPdfBill->value,compact('data_bill'));
                    }else{
                        $data_bill[0]['building_payment_info'] = $building_payment_info;
                        $data_bill[0]['building'] = $building;
                        $data_bill[0]['debit_detail'] = $debit_detail;
                        $data_bill[0]['apartment'] = $apartment;
                        $data_bill[0]['bill'] = $bill;
                        $data_bill[0]['buildingPlace'] = BuildingPlace::get_detail_bulding_place_by_bulding_place_id($apartment->building_place_id);
                        return view('bill.detail_bill_mau1', compact('data_bill'));
                    }
                return view('bill.detail_bill_mau1_old', compact('debit_detail', 'building', 'apartment', 'bill', 'total_paid', 'totalPaymentDebit','building_payment_info'));
            }
           
        }
        return response('Không tìm thấy hóa đơn hoặc hóa đơn đã bị xóa.');
    }

}
