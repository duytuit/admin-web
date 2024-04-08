<?php

namespace App\Http\Controllers\ReceiptTotal;

use App\Http\Controllers\BuildingController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\BdcReceipts\V2\ReceiptRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cookie;
use App\Repositories\Building\BuildingPlaceRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class ReceiptTotalController extends BuildingController
{
    protected $model;
    public $apartmentRepo;
    public $receiptRepo;
    private $modelBuildingPlace;
    public function __construct(
        Request $request,
        ApartmentsRespository $apartmentRepo,
        ReceiptRepository $receiptRepo,
        BuildingPlaceRepository $modelBuildingPlace
    ) {
        //$this->middleware('auth', ['except' => []]);
        //$this->middleware('route_permision');
        $this->apartmentRepo = $apartmentRepo;
        $this->receiptRepo = $receiptRepo;
        $this->modelBuildingPlace = $modelBuildingPlace;
        Carbon::setLocale('vi');
        parent::__construct($request);
    }

    public function index(Request $request)
    {
        $input = $request->all();
        $perPage = Cookie::get('per_page', 10);;

        $sumPhieuThu = $this->receiptRepo->cashBookMoneyWithTypePhieThu(ReceiptRepository::TIENMAT, $this->building_active_id, $input)->sum('cost');
        $sumPhieuChi = $this->receiptRepo->cashBookMoneyWithTypeV2(ReceiptRepository::TIENMAT, $this->building_active_id, $input)->sum('cost');
        // Dữ liệu Đầu kỳ
        if (isset($input['from_date'])) {
            $sumPT = $this->receiptRepo->cashBookMoneyDaukyWithTypePhieThu(ReceiptRepository::TIENMAT, $this->building_active_id, $input)->sum('cost');
            $sumPC = $this->receiptRepo->cashBookMoneyDauky($this->building_active_id, $input)->sum('cost');
            $sumDauKy = $sumPT - $sumPC;  
        } else {
            $sumDauKy = 0;
        }
        $rs = $this->receiptRepo->cashBookMoney($this->building_active_id, $input)->get();
        $receiptTotal = $this->receiptRepo->cashBookMoneyNew($this->building_active_id, $input);

        $receiptTotals = $receiptTotal->paginate($perPage);
        
        $ton = 0;
        
        foreach($rs as $key => $_rs) {
            if (($_rs->type != "phieu_chi" && $_rs->type != "phieu_chi_khac" && $_rs->type != "phieu_hoan_ky_quy" && $_rs->type_payment == ReceiptRepository::TIENMAT)) {
                $ton += $_rs->cost;
                $_rs->ton = $ton;
            }
            if (($_rs->type == "phieu_chi" && $_rs->type_payment == ReceiptRepository::TIENMAT) || ($_rs->type == "phieu_chi_khac" && $_rs->type_payment == ReceiptRepository::TIENMAT) || ($_rs->type == "phieu_hoan_ky_quy" && $_rs->type_payment == ReceiptRepository::TIENMAT)) {
                $ton -= $_rs->cost;
                $_rs->ton = $ton;
            }
            
            foreach($receiptTotals as $_receiptTotal) {
                // Nếu đầu kỳ = 0 và ví trí đầu tiền của mảng = 0 thì Tồn = 0
                if($sumDauKy == 0 && $key == 0) {
                    $_receiptTotal->ton = 0;
                    break;
                // Nếu đầu kỳ > 0 và ví trí đầu tiền của mảng = 0 thì Tồn = $sumDauKy + $rs[$i]['ton']
                } else if ($sumDauKy > 0 && $key == 0) {
                    $ton = $_receiptTotal->ton = $sumDauKy + $_rs->ton;
                } else if ($_rs->receipt_code == $_receiptTotal->receipt_code) {
                    $_receiptTotal->ton = $_rs->ton;
                    break;
                }
            }
        }
        
        $data['meta_title'] = 'Sổ quỹ tiền mặt';
        $data['apartments'] = $this->apartmentRepo->getApartmentOfBuilding($this->building_active_id);
        $data['receiptTotals'] = $receiptTotals;
        $data['thuTotals'] = $sumPhieuThu;
        $data['chiTotals'] = $sumPhieuChi;
        $data['dauKyTotals'] = $sumDauKy;
        $data['cuoiKyTotals'] = $sumDauKy + $sumPhieuThu - $sumPhieuChi;
        $data['filter'] = $input;
        if(isset($data['filter']['bdc_apartment_id'])){
            $data['get_apartment'] = $this->apartmentRepo->findById($data['filter']['bdc_apartment_id']);
         }
         if(isset($data['filter']['ip_place_id'])){
             $data['get_place_building'] = $this->modelBuildingPlace->findById($data['filter']['ip_place_id']);
         }
        $data['per_page'] = $perPage;
        
        return view('receipt-total.index', $data);
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
        return view('receipt-total.deposit_index', $data);
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

        return view('receipt-total.report', $data);
    }
}
