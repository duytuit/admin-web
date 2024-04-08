<?php

namespace App\Http\Controllers\BdcDebitLogs;

use App\Exceptions\QueueRedis;
use App\Http\Controllers\BuildingController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BdcDebitLogs\DebitLogs;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\BdcDebitLogs\DebitLogsRepository;
use App\Repositories\CronJobManager\CronJobManagerRepository;
use App\Repositories\Service\ServiceRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cookie;

class DebitLogsController extends BuildingController
{
    protected $model;
    public $apartmentRepo;
    public $serviceRepo;

    public function __construct(Request $request,  ServiceRepository $serviceRepo, ApartmentsRespository $apartmentRepo, DebitLogsRepository $model)
    {
        //$this->middleware('auth', ['except'=>[]]);
        //$this->middleware('route_permision');
        $this->serviceRepo = $serviceRepo;
        $this->model = $model;
        Carbon::setLocale('vi');
        parent::__construct($request);

        $this->apartmentRepo = $apartmentRepo;
    }

    public function importDienNuoc(Request $request)
    {
        $data['meta_title'] = 'Lịch sử hệ thống xử lý công nợ';
        $data['per_page'] = Cookie::get('per_page', 10);
        $month = [];
        $years[] = Carbon::now()->subYear(1)->format('Y');
        $years[] = Carbon::now()->format('Y');
        $years[] = Carbon::now()->addYear(1)->format('Y');
        foreach ($years as $key => $value) {
            for ($m=1; $m<=12; $m++) {
                $month[] = (int)date('Ym',strtotime($value.'-'.$m.'-01'));
           }
        }
        $data['serviceBuildingFilter'] = $this->serviceRepo->getServiceOfApartment_v3_log($this->building_active_id);
        $data['debitLogs'] = $this->model->filterBy($this->building_active_id, $request)->paginate($data['per_page']);
        $data['filter'] = $request->all();
        $data['month'] = $month;
        if ($this->building_active_id)
        {
            $data['apartments'] = $this->apartmentRepo->getApartmentOfBuildingDebit($this->building_active_id);
        }
        return view('debitLogs.importDienNuoc', $data);
    }
    public function action(Request $request,CronJobManagerRepository $cronJobManager)
    {
        $method = $request->input('method','');
        if ($method == 'per_page') {
            $this->per_page($request);
            return back();
        }
        if ($method == 'return_run_debit') {
            if(count( $request->ids) > 0){
             
               $log_debit = DebitLogs::whereIn('id',$request->ids)->get();
               $create_debit_process_v3 = 0;
               $dienuocdebitprocess_v2 = 0;
               $phidaukydebitprocess_v2 = 0;
               foreach ($log_debit as $key => $value) {
 
                    if($value->key == 'create_debit_process_v2:cron' ){
                        QueueRedis::setItemForQueue("add_queue_apartment_service_price_z_v2_{$value->bdc_building_id}", [json_decode($value->input)]);
                        if($create_debit_process_v3 == 0){
                            $cronJobManager->create([
                                'building_id' => $value->bdc_building_id,
                                'user_id' => auth()->user()->id,
                                'signature' => 'create_debit_process_v3:cron',
                                'status' => 0
                            ]);
                        }
                        $create_debit_process_v3 = 1;
                    }
                    if($value->key == 'dienuocdebitprocess_v2:cron' ){
                        $input = json_decode($value->input);
                        QueueRedis::setItemForQueue('add_queue_apartment_service_dien_nuoc_price_v2_' . $value->bdc_building_id, $input);
                        if($dienuocdebitprocess_v2 == 0){
                             $cronJobManager->create([
                                'building_id' => $value->bdc_building_id,
                                'user_id' => auth()->user()->id,
                                'signature' => 'dienuocdebitprocess_v2:cron',
                                'status' => 0,
                                'deadline' =>$input->deadline ? Carbon::parse($input->deadline)->format('Y-m-d') : Carbon::now()->format('Y-m-d')
                            ]);
                        }
                        $dienuocdebitprocess_v2 = 1;
                    }
                    if($value->key == 'phidaukydebitprocess_v2:cron' ){
                        $input = json_decode($value->input);
                        QueueRedis::setItemForQueue('add_queue_apartment_service_phi_dau_ky_v2_' . $value->bdc_building_id, $input);
                        if($phidaukydebitprocess_v2 == 0){
                            $cronJobManager->create([
                               'building_id' => $value->bdc_building_id,
                               'user_id' => auth()->user()->id,
                               'signature' => 'phidaukydebitprocess_v2:cron',
                               'status' => 0,
                               'deadline' =>$input->deadline ? Carbon::parse($input->deadline)->format('Y-m-d') : Carbon::now()->format('Y-m-d')
                           ]);
                       }
                       $phidaukydebitprocess_v2 = 1;
                    }
               }
            }
            return back()->with('success','đang xử lý lại'.count( $request->ids).' bản ghi');
        } 
    }
}
