<?php

namespace App\Http\Controllers\ReceiptTotal\V2;

use App\Http\Controllers\BuildingController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\BdcReceipts\V2\ReceiptRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cookie;
use App\Repositories\Building\BuildingPlaceRepository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Util\Debug\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class ReceiptTotalController extends BuildingController
{
    protected $model;
    public $apartmentRepo;
    public $receiptRepo;
    private $modelBuildingPlace;
    private $_modelUserInfo;
    public function __construct(
        Request $request,
        ApartmentsRespository $apartmentRepo,
        ReceiptRepository $receiptRepo,
        BuildingPlaceRepository $modelBuildingPlace,
        PublicUsersProfileRespository $modelUserInfo
    ) {
        //$this->middleware('auth', ['except' => []]);
        //$this->middleware('route_permision');
        $this->apartmentRepo = $apartmentRepo;
        $this->receiptRepo = $receiptRepo;
        $this->modelBuildingPlace = $modelBuildingPlace;
        $this->_modelUserInfo = $modelUserInfo;
        Carbon::setLocale('vi');
        parent::__construct($request);
    }

    public function index(Request $request)
    {
        $input = $request->all();
        $perPage = Cookie::get('per_page', 10);;

        $receiptTotals = $this->receiptRepo->cashBookMoneyV2($this->building_active_id, $request)->paginate($perPage);

        // Dữ liệu Đầu kỳ
        $sumDauKy = 0;
        if (isset($input['from_date'])) {
            $sumPT = $this->receiptRepo->cashBookMoneyDaukyWithTypePhieThu(ReceiptRepository::TIENMAT, $this->building_active_id, $input)->sum('cost');
            $sumPC = $this->receiptRepo->cashBookMoneyDauky($this->building_active_id, $input)->sum('cost');
            $sumDauKy = $sumPT - $sumPC;  
        }
        $sum_thu = 0;
        $sum_chi = 0;

        $sum_thu_dau_ky = 0;
        $sum_chi_dau_ky = 0;

        $sum_thu_trong_ky = 0;
        $sum_chi_trong_ky = 0;
        $sum_thu_1 = 0;
        $sum_chi_1 = 0;
        //dd($receiptTotals);
        if($receiptTotals->count() > 0){
            if($receiptTotals->currentPage() > 1){
                $first_item = $receiptTotals[0]->idNew;
                $cout=0;
                for ($i = $receiptTotals->currentPage() - 1; $i > 0; $i--) {
                    $first_item -= (int)$perPage;
                    $cout++;
                }
                $last_item = $first_item + $receiptTotals->total()-1;
                $sum_thu = $this->receiptRepo->sumThu($this->building_active_id,$first_item);
                $sum_chi = $this->receiptRepo->sumChi($this->building_active_id,$first_item);
                // $sum_thu_dau_ky = $this->receiptRepo->sumThuDauKy($this->building_active_id,$last_item);
                // $sum_chi_dau_ky = $this->receiptRepo->sumChiDauKy($this->building_active_id,$last_item);
                $sum_thu_trong_ky = $this->receiptRepo->sumThuTrongKy_Duongver($this->building_active_id,$first_item,$last_item,$request);
                $sum_chi_trong_ky = $this->receiptRepo->sumChiTrongKy($this->building_active_id,$first_item,$last_item);
            }else{
                $last_item = $receiptTotals[0]->idNew + $receiptTotals->total()-1;
                $sum_thu = $this->receiptRepo->sumThu($this->building_active_id,$receiptTotals[0]->idNew);
                $sum_chi = $this->receiptRepo->sumChi($this->building_active_id,$receiptTotals[0]->idNew);
                //dd($receiptTotals[$receiptTotals->count()-1]->idNew);
                // $sum_thu_dau_ky = $this->receiptRepo->sumThuDauKy($this->building_active_id,$last_item);
                // $sum_chi_dau_ky = $this->receiptRepo->sumChiDauKy($this->building_active_id,$last_item);
                $sum_thu_trong_ky = $this->receiptRepo->sumThuTrongKy_Duongver($this->building_active_id,$receiptTotals[0]->idNew,$last_item,$request);
                $sum_chi_trong_ky = $this->receiptRepo->sumChiTrongKy($this->building_active_id,$receiptTotals[0]->idNew,$last_item);
            }
            $sum_thu_1 = $this->receiptRepo->sumThu($this->building_active_id,$receiptTotals[0]->idNew);
            $sum_chi_1 = $this->receiptRepo->sumChi($this->building_active_id,$receiptTotals[0]->idNew);
        }
        $data['meta_title'] = 'Sổ quỹ tiền mặt';
        $data['apartments'] = $this->apartmentRepo->getApartmentOfBuilding($this->building_active_id);
        $data['receiptTotals'] = $receiptTotals;
        $data['thuTotals'] = $sum_thu_trong_ky;
        $data['chiTotals'] = $sum_chi_trong_ky;
        $data['dauKyTotals'] = $sumDauKy;
        $data['cuoiKyTotals'] = $sum_thu - $sum_chi;
        $data['cuoiKyTotals_1'] =$sum_thu_1 - $sum_chi_1;
        $data['filter'] = $input;
        $user_create_receipt = $this->receiptRepo->distinct_user_by_building($this->building_active_id);
        $data['user_info'] = $user_create_receipt ? $this->_modelUserInfo->getInfoByPubuserByBuildingId($user_create_receipt->toArray()) : null;
        if(isset($data['filter']['bdc_apartment_id'])){
            $data['get_apartment'] = $this->apartmentRepo->findById($data['filter']['bdc_apartment_id']);
         }
         if(isset($data['filter']['ip_place_id'])){
             $data['get_place_building'] = $this->modelBuildingPlace->findById($data['filter']['ip_place_id']);
         }
        $data['per_page'] = $perPage;
        
        return view('receipt-total.v2.index', $data);
    }

    public function indexbankbook (Request $request)
    {
        $input = $request->all();
        $perPage = Cookie::get('per_page', 10);;

        $receiptTotals = $this->receiptRepo->cashBookMoneyVerBanking($this->building_active_id, $request)->paginate($perPage);
        // Dữ liệu Đầu kỳ
        $sumDauKy = 0;
        if (isset($input['from_date'])) {
            $sumPT = $this->receiptRepo->cashBookMoneyDaukyWithTypePhieThu(ReceiptRepository::CHUYENKHOAN, $this->building_active_id, $input)->sum('cost');
            $sumPC = $this->receiptRepo->cashBookMoneyDaukyVerBanking($this->building_active_id, $input)->sum('cost');
            $sumDauKy = $sumPT - $sumPC;  
        }
        $sum_thu = 0;
        $sum_chi = 0;
        $sum_thu_dau_ky = 0;
        $sum_chi_dau_ky = 0;
        $sum_thu_trong_ky = 0;
        $sum_chi_trong_ky = 0;
        $sum_thu_1 = 0;
        $sum_chi_1 = 0;
        if($receiptTotals->count() > 0){
            if($receiptTotals->currentPage() > 1){
                $first_item = $receiptTotals[0]->idNew;
                $cout=0;
                for ($i = $receiptTotals->currentPage() - 1; $i > 0; $i--) {
                    $first_item -= (int)$perPage;
                    $cout++;
                }
                $last_item = $first_item + $receiptTotals->total()-1;
                $sum_thu = $this->receiptRepo->sumThuVerBanking($this->building_active_id,$first_item);
                $sum_chi = $this->receiptRepo->sumChiVerBanking($this->building_active_id,$first_item);
                $sum_thu_trong_ky = $this->receiptRepo->sumThuTrongKyVerBanking($this->building_active_id,$first_item,$last_item);
                $sum_chi_trong_ky = $this->receiptRepo->sumChiTrongKy($this->building_active_id,$first_item,$last_item);
            }else{
                $last_item = $receiptTotals[0]->idNew + $receiptTotals->total()-1;
                $sum_thu = $this->receiptRepo->sumThuVerBanking($this->building_active_id,$receiptTotals[0]->idNew);
                $sum_chi = $this->receiptRepo->sumChiVerBanking($this->building_active_id,$receiptTotals[0]->idNew);
                $sum_thu_trong_ky = $this->receiptRepo->sumThuTrongKyVerBanking($this->building_active_id,$receiptTotals[0]->idNew,$last_item);
                $sum_chi_trong_ky = $this->receiptRepo->sumChiTrongKyVerBanking($this->building_active_id,$receiptTotals[0]->idNew,$last_item);
            }
            $sum_thu_1 = $this->receiptRepo->sumThuVerBanking($this->building_active_id,$receiptTotals[0]->idNew);
            $sum_chi_1 = $this->receiptRepo->sumChiVerBanking($this->building_active_id,$receiptTotals[0]->idNew);
        }
        $data['meta_title'] = 'Sổ quỹ Ngân Hàng';
        $data['apartments'] = $this->apartmentRepo->getApartmentOfBuilding($this->building_active_id);
        $data['receiptTotals'] = $receiptTotals;
        $data['thuTotals'] = $sum_thu_trong_ky;
        $data['chiTotals'] = $sum_chi_trong_ky;
        $data['dauKyTotals'] = $sumDauKy;
        $data['cuoiKyTotals'] = $sum_thu - $sum_chi;
        $data['cuoiKyTotals_1'] =$sum_thu_1 - $sum_chi_1;
        $data['filter'] = $input;
        $user_create_receipt = $this->receiptRepo->distinct_user_by_building($this->building_active_id);
        $data['user_info'] = $user_create_receipt ? $this->_modelUserInfo->getInfoByPubuserByBuildingId($user_create_receipt->toArray()) : null;
        if(isset($data['filter']['bdc_apartment_id'])){
            $data['get_apartment'] = $this->apartmentRepo->findById($data['filter']['bdc_apartment_id']);
         }
         if(isset($data['filter']['ip_place_id'])){
             $data['get_place_building'] = $this->modelBuildingPlace->findById($data['filter']['ip_place_id']);
         }
        $data['per_page'] = $perPage;
        
        return view('receipt-bankbook.v2.index', $data);
    }
    
    public function reportReceiptDeposit(Request $request)
    {
        $data['meta_title'] = 'Sổ quỹ tiền mặt';
        $data['per_page'] = Cookie::get('per_page', 10);
        // Start displaying items from this number;
        $perPage = $data['per_page'];
        $data['filter'] = $request->all();
        if(isset($data['filter']['bdc_apartment_id'])){
            $data['get_apartment'] = $this->apartmentRepo->findById($data['filter']['bdc_apartment_id']);
        }
        if(isset($data['filter']['ip_place_id'])){
            $data['get_place_building'] = $this->modelBuildingPlace->findById($data['filter']['ip_place_id']);
        }
        $receiptDeposit = $this->receiptRepo->receiptReportDeposit($this->building_active_id, $request->all())->paginate($perPage);
      
        $data['receiptDeposit'] = $receiptDeposit;
        
        return view('receipt-total.v2.deposit_index', $data);
    }

    public function exportReceiptDeposit(Request $request)
    {
        return $this->receiptRepo->exportReportDeposit($this->building_active_id, $request->all());
    }

    public function report(Request $request)
    {
        $input = $request->all();
        $receiptTotal = $this->receiptRepo->receiptTotal($this->building_active_id, $input);

        $receiptTotalCollectConvert = collect($receiptTotal);
        $_receiptTotal = $receiptTotal;
        // Dữ liệu đầu kỳ
        $firstIndex = array_pop($_receiptTotal);
        $receiptDauKy = $this->receiptRepo->receiptDauKy($this->building_active_id, $firstIndex);
        $receiptDauKyCollectConvert = collect($receiptDauKy);
        // $sumThu = array_sum(array_column($receiptTotal, 'sumery'));
        // $sumChi = array_sum(array_column($receiptTotal, 'sumery'));

        $sumPhieuThu = $receiptTotalCollectConvert->where('type', '!=', 'phieu_chi')->sum('cost');
        // dd($receiptTotalCollectConvert);
        $sumPhieuChi = $receiptTotalCollectConvert->where('type', 'phieu_chi')->sum('cost');
        $sumPhatSinh = $receiptTotalCollectConvert->sum('sumery');
        // Dữ liệu Đầu kỳ
        if ($receiptDauKyCollectConvert->isNotEmpty()) {
            $sumPhieuThuDauKy = $receiptDauKyCollectConvert->where('type', '!=', 'phieu_chi')->sum('cost');
            $sumPhieuChiDauKy = $receiptDauKyCollectConvert->where('type', 'phieu_chi')->sum('cost');
            $sumPhatSinhDauKy = $receiptDauKyCollectConvert->sum('sumery');
            $sumDauKy = $sumPhieuThuDauKy + $sumPhieuChiDauKy + $sumPhatSinhDauKy;
        } else {
            $sumDauKy = $firstIndex->sumery + $firstIndex->cost;
        }
        // PTTGV_0000215
        // PTTGV_0000211
        // Get the current page from the url if it's not set default to 1
        $page = array_key_exists('page', $input) && $input['page'] > 0 ? $input['page'] : 1;
        // Number of items per page
        $perPage = 20;
        // Start displaying items from this number;
        $offSet = ($page * $perPage) - $perPage; // Start displaying items from this number
        // Get only the items you need using array_slice (only get 10 items since that's what you need)
        $itemsForCurrentPage = array_slice($receiptTotal, $offSet, $perPage, true);

        $pagination = new \Illuminate\Pagination\LengthAwarePaginator($itemsForCurrentPage, count($receiptTotal), $perPage, $page);
        $pagination->setPath('/admin/receipt-total');

        $data['meta_title'] = 'Thu chi tổng hợp';
        $data['apartments'] = $this->apartmentRepo->getApartmentOfBuilding($this->building_active_id);
        $data['receiptTotals'] = $pagination;
        $data['thuTotals'] = $sumPhieuThu;
        $data['chiTotals'] = $sumPhieuChi;
        $data['phatSinhTotals'] = $sumPhatSinh;
        $data['dauKyTotals'] = $sumDauKy;
        $data['cuoiKyTotals'] = $sumDauKy + $sumPhatSinh + $sumPhieuChi - $sumPhieuThu;
        $data['filter'] = $input;
        $data['per_page'] = $page;

        return view('receipt-total.v2.report', $data);
    }
}
