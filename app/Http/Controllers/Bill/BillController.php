<?php

namespace App\Http\Controllers\Bill;

use App\Http\Controllers\BuildingController;
use App\Models\Campain;
use App\Models\SentStatus;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\BdcBills\V2\BillRepository;
use App\Repositories\BdcDebitDetail\DebitDetailRepository;
use App\Repositories\BdcV2DebitDetail\DebitDetailRepository as BdcV2DebitDetailDebitDetailRepository;
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

class BillController extends BuildingController
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
        parent::__construct($request);
        //$this->middleware('route_permision');
        $this->billRepo = $billRepo;
        $this->debitDetailRepo = $debitDetailRepo;
        $this->buildingRepo = $buildingRepo;
        $this->customerRepo = $customerRepo;
        $this->profileRepo = $profileRepo;
        $this->apartmentRepo = $apartmentRepo;
        $this->modelBuildingPlace = $modelBuildingPlace;
    }

    public function index(Request $request)
    {
        $data['meta_title'] = 'Quản lý hóa đơn';
        //$data['buildings'] = $this->buildingRepo->getBuildingOfUser($this->building_active_id);
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['filter'] = $request->all();
        if(isset($data['filter']['bdc_apartment_id'])){
            $data['get_apartment'] = $this->apartmentRepo->findById($data['filter']['bdc_apartment_id']);
         }
         if(isset($data['filter']['ip_place_id'])){
             $data['get_place_building'] = $this->modelBuildingPlace->findById($data['filter']['ip_place_id']);
         }
        $bills_filter = $this->billRepo->filterBillv2($this->building_active_id, $request->all())->paginate($data['per_page']);
        $data['bills'] = $bills_filter;
        return view('bill.indexv2', $data);
    }

    public function waitForConfirm(Request $request, DebitDetailRepository $xx)
    {
        
        $data['meta_title'] = 'Duyệt số liệu';
        $data['buildings'] = $this->buildingRepo->getBuildingOfUser($this->building_active_id);
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['bills'] = $this->billRepo->filterWaitForConfirm($this->building_active_id, $request->all(), $data['per_page']);
        $data['status'] = BillRepository::WAIT_FOR_CONFIRM;
        $data['filter'] = $request->all();
        if(isset($data['filter']['bdc_apartment_id'])){
            $data['get_apartment'] = $this->apartmentRepo->findById($data['filter']['bdc_apartment_id']);
         }
         if(isset($data['filter']['ip_place_id'])){
             $data['get_place_building'] = $this->modelBuildingPlace->findById($data['filter']['ip_place_id']);
         }
        if ($this->building_active_id)
        {
            $data['apartments'] = $this->apartmentRepo->getApartmentOfBuildingDebit($this->building_active_id);
        }

        return view('bill.wait_for_confirm', $data);
    }
    public function waitForConfirmEditDateline(Request $request, DebitDetailRepository $xx)
    {
        
        $data['meta_title'] = 'Duyệt số liệu';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['filter'] = $request->all();
        if(isset($data['filter']['bdc_apartment_id'])){
            $data['get_apartment'] = $this->apartmentRepo->findById($data['filter']['bdc_apartment_id']);
         }
         if(isset($data['filter']['ip_place_id'])){
             $data['get_place_building'] = $this->modelBuildingPlace->findById($data['filter']['ip_place_id']);
         }
        $page = $request->page ? $request->page : 1;
        $perPage = $data['per_page'];
        $offSet = ($page * $perPage) - $perPage;
        $cycle_names = BdcV2DebitDetailDebitDetailRepository::getCycleName($this->building_active_id);
        $data['cycle_names'] = $cycle_names;
        $data['chose_cycle_name'] = $request->cycle_name ? $request->cycle_name :  '';
        $data['bills'] = $this->billRepo->getBillv2($this->building_active_id, $request->all())->paginate($data['per_page']);
        return view('bill.wait_for_confirm_edit_dateline', $data);
    }

    public function waitToSend(Request $request)
    {
        $data['meta_title'] = 'Gửi thông báo';
        $data['buildings'] = $this->buildingRepo->getBuildingOfUser($this->building_active_id);
        $data['per_page'] = Cookie::get('per_page', 10);
        $cycle_names = $this->debitDetailRepo->getCycleNameV2($this->building_active_id);
        $data['cycle_names'] = $cycle_names;
        $data['chose_cycle_name'] = $request->cycle_name ? $request->cycle_name : ($cycle_names ? $cycle_names[0] : '');
        $data['bills'] = $this->billRepo->filterWaitToSend($this->building_active_id,$data['chose_cycle_name'],$request->all(), $data['per_page']);
        $data['status'] = BillRepository::WAIT_TO_SEND;
        $data['filter'] = $request->all();

        if(isset($data['filter']['bdc_apartment_id'])){
            $data['get_apartment'] = $this->apartmentRepo->findById($data['filter']['bdc_apartment_id']);
         }
         if(isset($data['filter']['ip_place_id'])){
             $data['get_place_building'] = $this->modelBuildingPlace->findById($data['filter']['ip_place_id']);
         }
        if ($this->building_active_id)
        {
            $data['apartments'] = $this->apartmentRepo->getApartmentOfBuildingDebit($this->building_active_id);
        }

        return view('bill.wait_to_send', $data);
    }

    public function listPay(Request $request)
    {
        $data['meta_title'] = 'Danh sách bảng kê';
        $data['buildings'] = $this->buildingRepo->getBuildingOfUser($this->building_active_id);
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['bills'] = $this->billRepo->filterPay($this->building_active_id, $request->all(), $data['per_page']);
        $data['status'] = BillRepository::PAYING;
        $data['filter'] = $request->all();
        if(isset($data['filter']['bdc_apartment_id'])){
            $data['get_apartment'] = $this->apartmentRepo->findById($data['filter']['bdc_apartment_id']);
         }
         if(isset($data['filter']['ip_place_id'])){
             $data['get_place_building'] = $this->modelBuildingPlace->findById($data['filter']['ip_place_id']);
         }
        if ($this->building_active_id)
        {
            $data['apartments'] = $this->apartmentRepo->getApartmentOfBuildingDebit($this->building_active_id);
        }

        return view('bill.list_pay', $data);
    }
    public function destroyBill($id)
    {
        $resultBillDel = $this->billRepo->delete(['id' => $id]);
        if($resultBillDel > 0){
           $GetDebitDetail = $this->debitDetailRepo->filterBillId($id);
            $data_list = array();
            foreach ($GetDebitDetail as $value) {
                array_push($data_list, $value->id);
            }
           if($data_list){
               $this->debitDetailRepo->deleteAt($data_list);
           }
             return redirect()->route('admin.bill.listPay')->with('success', 'xóa bảng kê thành công!');
        }else{
             return redirect()->route('admin.bill.listPay')->with('error', 'xóa bảng kê thất bại!');
        }
    }
    public function show($id, Request $request)
    {
        if( $id == 0 ) {
            // dùng khi truyền param bill_code
            // admin/bill/show/0?bil_code=DKM_0000003
            $bill = $this->billRepo->findBillCode($this->building_active_id, $request->input('bill_code'));
            $id = $bill->id;
        } else {
            $bill = $this->billRepo->find($id);
        }

        if (!$bill) {
            return abort(404);
        }
        $data['meta_title'] = 'Chi tiết hóa đơn';
        $data['bill'] = $bill;
        $data['debit_detail'] = $this->debitDetailRepo->getDetailBillId($id);
        // $data['dashboard'] = $this->billRepo->dashboardBill($this->building_active_id, $this->debitDetailRepo);
        return view('bill.show', $data);
    }

    public function export(Request $request)
    {
        return $this->billRepo->export($this->building_active_id, $request);
    }

    public function exportFilter(Request $request) {
        return $this->billRepo->filterBillExport($this->building_active_id, $request->all());
    }
    public function exportFilterBangKeKhachHang(Request $request) {
        return $this->billRepo->filterBillExportBangKeKhachHang($this->building_active_id, $request->all());
    }

    public function changeStatus(Request $request)
    {
        if ($request->ids)
        {
            $status = $this->billRepo->changeMultiStatus($request->ids);
            return response()->json($status);
        }
    }

    public function postChangeStatus(Request $request, BillRepository $billRepository, ConfigRepository $configRepository, DebitDetailRepository $debitRepo)
    {
        if ($request->ids)
        {
            try {
                $bills = $this->billRepo->postChangeMultiStatus($request->ids,$debitRepo);
                if($bills)
                {
                    // ghi file PDF với trạng thái = -2
                
                        // foreach($bills as $bill)
                        // {
                        //     if($bill->status == $billRepository::WAIT_TO_SEND)
                        //     {
                        //         $building = $bill->building;
                        //         $apartment = $bill->apartment;
                        //         $debit_detail = $this->debitDetailRepo->getDetailBillId($bill->id);
                        //         $totalPaymentDebit = $this->debitDetailRepo->findMaxVersionWithBuildingApartment($this->building_active_id, $bill->bdc_apartment_id, $bill->id);
                        //         $pathPdf = $_SERVER['DOCUMENT_ROOT'] . "/bang-ke/$bill->bill_code.pdf";
                        //         $configPdfBill = $configRepository->findByKeyActiveFirst($this->building_active_id, $configRepository::BANGKE_PDF);
                        //         $debitDetails = $this->debitDetailRepo->findMaxVersionPaid($bill->id);
                        //         if($configPdfBill) {
                        //             if($configPdfBill->value == "mau_1") {
                        //                 $pdf = PDF::loadView('bill.pdf_mau1', compact('debit_detail', 'building', 'apartment', 'bill', 'debitDetails', 'totalPaymentDebit'));
                        //             } else {
                        //                 $pdf = PDF::loadView('bill.pdf_mau2', compact('debit_detail', 'building', 'apartment', 'bill', 'debitDetails', 'totalPaymentDebit'));
                        //             }
                        //         } else {
                        //             $pdf = PDF::loadView('bill.pdf_mau1', compact('debit_detail', 'building', 'apartment', 'bill', 'debitDetails', 'totalPaymentDebit'));
                        //         }
                        //         $pdf->save($pathPdf);
                        //     }
                        // }
                
                }
            } catch (\Exception $e) {

                    return $e->getMessage();
            }
            $dataResponse = [
                'success' => true,
                'message' => 'Thay đổi trạng thái thành công!',
            ];

            return response()->json($dataResponse);
        }
    }

    public function sendMail($email, $type=69){

        $total = ['email'=>1, 'app'=> 0, 'sms'=> 0];
        $campain = Campain::updateOrCreateCampain("Gửi email cho ".$email, config('typeCampain.BILL'), null, $total, $this->building_active_id, 0, 0);

         
        $data = [
          'params' => [
              '@ngay' => date('d/m/Y',time()),
          ],
          'cc'=>$email,
          'type'=>$type,
          'status'=>'success',
          'campain_id' => $campain->id
        ];
        try {
            ServiceSendMailV2::setItemForQueue($data);
            return;
        } catch (\Exception $e){
            return $e->getMessage();
        }
    }

    public function reloadPdf(Request $request, ConfigRepository $configRepository)
    {
        $bill = $this->billRepo->findBillCode($this->building_active_id, $request->billCode);
        try {
            if($bill)
            {
                $pdfName = $bill->bill_code;
                $pathPdf = $_SERVER['DOCUMENT_ROOT'] . "/bang-ke/$pdfName.pdf";
                $urlPdf = "bang-ke/$pdfName.pdf";
                $_bill = $this->billRepo->find($bill->id);
                $_bill->url = $urlPdf;
                $_bill->save();
                $building = $bill->building;
                $apartment = $bill->apartment;
                $debit_detail = $this->debitDetailRepo->getDetailBillId($bill->id);
                $totalPaymentDebit = $this->debitDetailRepo->findMaxVersionWithBuildingApartment($this->building_active_id, $bill->bdc_apartment_id, $bill->id);
                $configPdfBill = $configRepository->findByKeyActiveFirst($this->building_active_id, $configRepository::BANGKE_PDF);
                $debitDetails = $this->debitDetailRepo->findMaxVersionPaid($bill->id);
                if($configPdfBill) {
                    if($configPdfBill->value == "mau_1") {
                        $pdf = PDF::loadView('bill.pdf_mau1', compact('debit_detail', 'building', 'apartment', 'bill', 'debitDetails', 'totalPaymentDebit'));
                    } else {
                        $pdf = PDF::loadView('bill.pdf_mau2', compact('debit_detail', 'building', 'apartment', 'bill', 'debitDetails', 'totalPaymentDebit'));
                    }
                } else {
                    $pdf = PDF::loadView('bill.pdf_mau1', compact('debit_detail', 'building', 'apartment', 'bill', 'debitDetails', 'totalPaymentDebit'));
                }
                $pdf->save($pathPdf);
                $dataResponse = [
                    'success' => true,
                    'message' => 'Cập nhật file PDF thành công!',
                ];
                return response()->json($dataResponse);       
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        $dataResponse = [
            'success' => false,
            'message' => "Mã bảng kê $request->billCode không tồn tại.",
        ];
        return response()->json($dataResponse);       
    }


    public function reloadpdfv2(Request $request, ConfigRepository $configRepository)
    {
            try {
                if(isset($request->ids)){
                    $list_ids = $request->ids;
                    foreach ($list_ids as $key => $value) {
                           $_bill = $this->billRepo->find($value);
                           //$base_url=((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http"). "://". @$_SERVER['HTTP_HOST'];
                           $urlPdf = "admin/bill/detail/".$_bill->bill_code;
                           $_bill->url = $urlPdf;
                           $_bill->save();
                    }
                }
                return redirect()->route('admin.bill.listPay')->with('success', 'reload view thành công!!');
            } catch (Exception $e) {
                return redirect()->route('admin.bill.listPay')->with('error', 'reload view không thành công!!');
            }
          
    }
    public function reloadpdfv3(Request $request, ConfigRepository $configRepository)
    {
            try {
                if(isset($request->ids)){
                    $list_ids = $request->ids;
                    foreach ($list_ids as $key => $value) {
                           $_bill = $this->billRepo->find($value);
                           $pdfName = $_bill->bill_code;
                           $pathPdf = $_SERVER['DOCUMENT_ROOT'] . "/bang-ke/$pdfName.pdf";
                           $urlPdf = "bang-ke/$pdfName.pdf";
                           $_bill->url = $urlPdf;
                           $_bill->save();
                           $building = $_bill->building;
                           $apartment = $_bill->apartment;
                           $debit_detail = $this->debitDetailRepo->getDetailBillId($_bill->id);
                           $totalPaymentDebit = $this->debitDetailRepo->findMaxVersionWithBuildingApartment($this->building_active_id, $_bill->bdc_apartment_id, $_bill->id);
                           $configPdfBill = $configRepository->findByKeyActiveFirst($this->building_active_id, $configRepository::BANGKE_PDF);
                           $debitDetails = $this->debitDetailRepo->findMaxVersionPaid($_bill->id);
                           if($configPdfBill) {
                               if($configPdfBill->value == "mau_1") {
                                   $pdf = PDF::loadView('bill.pdf_mau1', compact('debit_detail', 'building', 'apartment', '_bill', 'debitDetails', 'totalPaymentDebit'));
                               } else {
                                   $pdf = PDF::loadView('bill.pdf_mau2', compact('debit_detail', 'building', 'apartment', '_bill', 'debitDetails', 'totalPaymentDebit'));
                               }
                           } else {
                               $pdf = PDF::loadView('bill.pdf_mau1', compact('debit_detail', 'building', 'apartment', '_bill', 'debitDetails', 'totalPaymentDebit'));
                           }
                           $pdf->save($pathPdf);
                    }
                }
                return redirect()->route('admin.bill.listPay')->with('success', 'Cập nhật file PDF thành công!!');
            } catch (Exception $e) {
                return redirect()->route('admin.bill.listPay')->with('error', 'Cập nhật file PDF không thành công!!');
            }
          
    }

    public function delete(Request $request, DebitDetailRepository $debitDetailRepository)
    {
        try {
            $bill = $this->billRepo->deleteBill($request->id, $debitDetailRepository);
            if($bill) {
                return redirect()->back()->with(['success' => 'Xóa bảng kê thành công!']);
            } else {
                return redirect()->back()->with(['error' => 'Không thể xóa bảng kê khi chưa xóa hết Chi tiết Bảng kê - Dịch vụ.']);
            }            
        } catch (Exception $e) {
            return redirect()->back()->with(['error' => $e->getMessage()]);
        }
        
    }
}
