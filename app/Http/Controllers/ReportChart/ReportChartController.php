<?php

namespace App\Http\Controllers\ReportChart;

use App\Commons\Api;
use App\Commons\Helper;
use App\Http\Controllers\BuildingController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\BdcDebitDetail\V2\DebitDetailRepository;
use App\Repositories\BdcReceipts\V2\ReceiptRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cookie;
use App\Repositories\Building\BuildingPlaceRepository;
use App\Repositories\Service\ServiceRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class ReportChartController extends BuildingController
{
    protected $model;
    public $apartmentRepo;
    public $receiptRepo;
    public $serviceRepo;
    public $modelBuildingPlace;
    public function __construct(
        Request $request,
        ApartmentsRespository $apartmentRepo,
        ReceiptRepository $receiptRepo,
        BuildingPlaceRepository $modelBuildingPlace,
        ServiceRepository $serviceRepo
    ) {
        //$this->middleware('route_permision');
        $this->apartmentRepo = $apartmentRepo;
        $this->receiptRepo = $receiptRepo;
        $this->modelBuildingPlace = $modelBuildingPlace;
        $this->serviceRepo = $serviceRepo;
        Carbon::setLocale('vi');
        parent::__construct($request);
    }

    public function index(Request $request)
    {
        $perPage = Cookie::get('per_page', 10);
        $data['meta_title'] = 'Báo cáo thông kê tổng hợp công nợ';
        $data['filter'] =  $request->all();
        $data['per_page'] = $perPage;

        if(isset($data['filter']['apartment_id'])){
            $data['get_apartment'] = $this->apartmentRepo->findById($data['filter']['apartment_id']);
        }
        if(isset($data['filter']['building_place'])){
            $data['get_place_building'] = $this->modelBuildingPlace->findById($data['filter']['building_place']);
        }
        if(isset($data['filter']['service_id'])){
            $data['get_service'] = $this->serviceRepo->findServiceV2($data['filter']['service_id']);
        }

        $data['serviceBuildingFilter'] = $this->serviceRepo->getServiceOfApartment_v2($this->building_active_id);

        $getFirstDayOfYear = Carbon::now()->subMonth(12)->format('Y-m-d');
        $getLastDayOfYear = Carbon::now()->format('Y-m-d');
        $request->request->add(['building_id' => $this->building_active_id]);
        $request->request->add(['from_date' => $getFirstDayOfYear]);
        $request->request->add(['to_date' => $getLastDayOfYear]);
      
        $array_search='';
        $i=0;
        foreach ($request->all() as $key => $value) {
            if($i == 0){
                $param='?'.$key.'='.(string)$value;
            }else{
                $param='&'.$key.'='.(string)$value;
            }
            $i++;
            $array_search.=$param;
        }
        $data['array_search'] = $array_search;
        $data['loai_phi_dich_vu'] = Helper::loai_phi_dich_vu;
        return view('report-chart.index', $data);
    }
    public function report_total_interactive(Request $request)
    {
        $perPage = Cookie::get('per_page', 10);
        $data['meta_title'] = 'Tổng hợp dữ liệu';
        $data['filter'] =  $request->all();
        $data['per_page'] = $perPage;

        if(isset($data['filter']['apartment_id'])){
            $data['get_apartment'] = $this->apartmentRepo->findById($data['filter']['apartment_id']);
        }
        if(isset($data['filter']['building_place'])){
            $data['get_place_building'] = $this->modelBuildingPlace->findById($data['filter']['building_place']);
        }
        if(isset($data['filter']['service_id'])){
            $data['get_service'] = $this->serviceRepo->findServiceV2($data['filter']['service_id']);
        }

        $data['serviceBuildingFilter'] = $this->serviceRepo->getServiceOfApartment_v2($this->building_active_id);

        $getFirstDayOfYear = Carbon::now()->subMonth(12)->format('Y-m-d');
        $getLastDayOfYear = Carbon::now()->format('Y-m-d');
        $request->request->add(['building_id' => $this->building_active_id]);
        $request->request->add(['from_date' => $getFirstDayOfYear]);
        $request->request->add(['to_date' => $getLastDayOfYear]);
        $getStatFeedback =  Api::GET('admin/getStatFeedback',$request->all());
        $getStatVote =  Api::GET('admin/getStatVote',$request->all());
        $getNotifyEvent =  Api::GET('admin/getNotifyEvent',$request->all());
        $sum_count_feedback=0;
        if($getStatFeedback && $getStatFeedback->status == true && count($getStatFeedback->data)){
            $list_status = null;
            foreach ($getStatFeedback->data as $key => $value) {
                $sum_count_feedback += $value->count;
                $list_status[]= Helper::trang_thai[$value->status];

            }
            $count_list =  array_column($getStatFeedback->data,'count');
            $chartjs = app()->chartjs
            ->name('getStatFeedback')
            ->type('pie')
            ->labels($list_status)
            ->datasets([
                [
                    "label" => "biểu đồ", // sumery
                    'backgroundColor' => ['#3214C1', '#A464CF', '#91E2EE', '#DEB0B2', '#D1C7A0','#FFCC33','#33CC00','#FF99FF','#CC9900','#CC9999','#999900','#669900','#CC6633','#FF0066','#00DD00','#330066'],
                    'data' => $count_list
                ]
            ])
            ->options(['responsive' => true,'maintainAspectRatio'=>true]);
        }
        $sum_count_vote=0;
        if($getStatVote && $getStatVote->status == true && count($getStatVote->data)){
            $list_vote = null;
            foreach ($getStatVote->data as $key => $value) {
                $sum_count_vote += $value->count;
                $list_vote[]= Helper::vote[$value->vote];

            }
            $count_list =  array_column($getStatVote->data,'count');
            $chartjs_1 = app()->chartjs
            ->name('getStatVote')
            ->type('pie')
            ->labels($list_vote)
            ->datasets([
                [
                    "label" => "biểu đồ", // sumery
                    'backgroundColor' => ['#FFCC33','#33CC00','#FF99FF','#CC9900','#CC9999','#999900','#669900','#CC6633','#FF0066','#00DD00','#330066'],
                    'data' => $count_list
                ]
            ])
            ->options(['responsive' => true,'maintainAspectRatio'=>true]);
        }
        $data['chartjs'] = isset($chartjs) ? $chartjs : null;
        $data['chartjs_1'] = isset($chartjs_1) ? $chartjs_1 : null;
        $data['sum_count_feedback'] = $sum_count_feedback;
        $data['sum_count_vote'] = $sum_count_vote;
        $data['getNotifyEvent'] = $getNotifyEvent;
        return view('report-chart.report-total-interactive', $data);
    }
    public function report_total_data_building(Request $request)
    {
        $perPage = Cookie::get('per_page', 10);
        $data['meta_title'] = 'Tổng hợp dữ liệu';
        $data['filter'] =  $request->all();
        $data['per_page'] = $perPage;

        if(isset($data['filter']['apartment_id'])){
            $data['get_apartment'] = $this->apartmentRepo->findById($data['filter']['apartment_id']);
        }
        if(isset($data['filter']['building_place'])){
            $data['get_place_building'] = $this->modelBuildingPlace->findById($data['filter']['building_place']);
        }
        if(isset($data['filter']['service_id'])){
            $data['get_service'] = $this->serviceRepo->findServiceV2($data['filter']['service_id']);
        }

        $data['serviceBuildingFilter'] = $this->serviceRepo->getServiceOfApartment_v2($this->building_active_id);

        $getFirstDayOfYear = Carbon::now()->subMonth(12)->format('Y-m-d');
        $getLastDayOfYear = Carbon::now()->format('Y-m-d');
        $request->request->add(['building_id' => $this->building_active_id]);
        $request->request->add(['from_date' => $getFirstDayOfYear]);
        $request->request->add(['to_date' => $getLastDayOfYear]);
        $getStatVehicle = Api::GET('admin/getStatVehicle',$request->all());
        $getStatBuilding = Api::GET('admin/getStatBuilding',$request->all());
        $getStatVehicleReg = Api::GET('admin/getStatVehicleReg',$request->all());
    
        if($getStatVehicleReg->status == true && count($getStatVehicleReg->data)){
            $cycle_name_list = array_column($getStatVehicleReg->data,'date');
            $register_list =  array_column($getStatVehicleReg->data,'register');
            $cancel_list =  array_column($getStatVehicleReg->data,'cancel');
            $chartjs = app()->chartjs
            ->name('barChartTest')
            ->type('bar')
            ->labels(array_values($cycle_name_list))
            ->datasets([
                [
                   "label" => "Vào", // vào
                   'backgroundColor' => ['#FD9670'],
                    'data' => $register_list
                ],
                [
                    "label" => "Ra", // ra
                    'backgroundColor' => ['#6997F8'],
                    'data' => $cancel_list
                ]
            ])
            ->options(['responsive' => true,
                'scales' => [
                    'y' => [
                        'display' => true,
                        'title' => [
                            'display' => true,
                            'text' => 'Xe',
                        ],
                        'max' => (int)max($register_list)*2
                    ],
                    'x' => [
                        'display' => true,
                        'title' => [
                            'display' => true,
                            'text' => 'Tháng',
                        ]
                    ]
                ]

            ]);
        }
        $sum_count=0;
        if($getStatVehicle && $getStatVehicle->status == true && count($getStatVehicle->data)){
            foreach ($getStatVehicle->data as $key => $value) {
                $sum_count += $value->count;
            }
            $name_list =  array_column($getStatVehicle->data,'name');
            $count_list =  array_column($getStatVehicle->data,'count');
            $chartjs_1 = app()->chartjs
            ->name('getStatVehicle')
            ->type('pie')
            ->labels($name_list)
            ->datasets([
                [
                    "label" => "biểu đồ nợ", // sumery
                    'backgroundColor' => ['#3214C1', '#A464CF', '#91E2EE', '#DEB0B2', '#D1C7A0','#FFCC33','#33CC00','#FF99FF','#CC9900','#CC9999','#999900','#669900','#CC6633','#FF0066','#00DD00','#330066'],
                    'data' => $count_list
                ]
            ])
            ->options(['responsive' => true,'maintainAspectRatio'=>true]);
        }
        $data['chartjs_1'] = isset($chartjs_1) ? $chartjs_1 : null;
        $data['chartjs'] = isset($chartjs) ? $chartjs : null;
        $data['sum_count'] = $sum_count;
        $data['getStatBuilding'] = isset($getStatBuilding) ? $getStatBuilding : null;
        return view('report-chart.report-total-data-building', $data);
    }
    public function report_total_cash(Request $request)
    {
        $perPage = Cookie::get('per_page', 10);
        $data['meta_title'] = 'Tổng hợp tiền mặt';
        $data['filter'] =  $request->all();
        $data['per_page'] = $perPage;

        if(isset($data['filter']['apartment_id'])){
            $data['get_apartment'] = $this->apartmentRepo->findById($data['filter']['apartment_id']);
        }
        if(isset($data['filter']['building_place'])){
            $data['get_place_building'] = $this->modelBuildingPlace->findById($data['filter']['building_place']);
        }
        if(isset($data['filter']['service_id'])){
            $data['get_service'] = $this->serviceRepo->findServiceV2($data['filter']['service_id']);
        }

        $data['serviceBuildingFilter'] = $this->serviceRepo->getServiceOfApartment_v2($this->building_active_id);

        $getFirstDayOfYear = Carbon::now()->subMonth(12)->format('Y-m-d');
        $getLastDayOfYear = Carbon::now()->format('Y-m-d');
        $request->request->add(['building_id' => $this->building_active_id]);
        $request->request->add(['from_date' => $getFirstDayOfYear]);
        $request->request->add(['to_date' => $getLastDayOfYear]);

        $getCashFlow = Api::GET('admin/getCashFlow',$request->all());
        $sum_cost = 0;
        if($getCashFlow && $getCashFlow->status == true && count($getCashFlow->data)){
            $list_payment = null;
            foreach ($getCashFlow->data as $key => $value) {
                $sum_cost += $value->cost;
                $list_payment[]= Helper::loai_danh_muc[$value->type_payment];

            }
            $cost =  array_column($getCashFlow->data, 'cost');
            $chartjs_1 = app()->chartjs
            ->name('getCashFlow')
            ->type('pie')
            ->labels($list_payment)
            ->datasets([
                [
                    "label" => "biểu đồ", // sumery
                    'backgroundColor' => ['#3214C1', '#A464CF', '#91E2EE', '#DEB0B2', '#D1C7A0','#FFCC33','#33CC00','#FF99FF','#CC9900','#CC9999','#999900','#669900','#CC6633','#FF0066','#00DD00','#330066'],
                    'data' => $cost
                ]
            ])
            ->options(['responsive' => true,'maintainAspectRatio'=>true]);
        }
        $data['chartjs'] = isset($chartjs) ? $chartjs : null;
        $data['chartjs_1'] = isset($chartjs_1) ? $chartjs_1 : null;
        $data['sum_cost'] = $sum_cost;

        $array_search='';
        $i=0;
        foreach ($request->all() as $key => $value) {
            if($i == 0){
                $param='?'.$key.'='.(string)$value;
            }else{
                $param='&'.$key.'='.(string)$value;
            }
            $i++;
            $array_search.=$param;
        }
        $data['array_search'] = $array_search;
        $data['loai_danh_muc'] = Helper::loai_danh_muc;
        return view('report-chart.report-total-cash', $data);
    }
}
