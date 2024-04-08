<?php

namespace App\Repositories\BdcBills\V2;

use App\Commons\Util\Debug\Log;
use App\Exceptions\QueueRedis;
use App\Helpers\dBug;
use App\Models\Apartments\Apartments;
use App\Models\BdcV2DebitDetail\DebitDetail;
use App\Models\Building\Building;
use App\Models\Campain;
use App\Models\Fcm\Fcm;
use App\Repositories\Eloquent\Repository;
use App\Services\ServiceSendMailV2;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\FCM\SendNotifyFCMService;
use App\Repositories\NotifyLog\NotifyLogRespository;
const PAGE = 10;
const BUILDING_USER = 1;
const FAIL = 0;
use App\Models\PublicUser\Users;
use App\Models\SentStatus;
use App\Models\Service\Service;
use App\Models\Vehicles\Vehicles;
use App\Repositories\BdcApartmentServicePrice\ApartmentServicePriceRepository;
use App\Repositories\BdcDebitDetail\V2\DebitDetailRepository;
use App\Repositories\BdcV2DebitDetail\DebitDetailRepository as BdcV2DebitDetailDebitDetailRepository;
use App\Repositories\Customers\CustomersRespository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;

class BillRepository extends Repository
{
    const BILL_NEW = "bill";
    const WAIT_FOR_CONFIRM = -3;
    const WAIT_TO_SEND = -2;
    const PAYING = 1;
    const PAID = 2;
    const OUT_OF_DATE = 3;

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\BdcBills\Bills::class;
    }

    function configs()
    {
        return \App\Repositories\Config\ConfigRepository::class;
    }

    public function getAllOfBuilding($perPage, $building)
    {
        $monthNow = Carbon::now()->month;
        $bills = $this->model->whereMonth('created_at', $monthNow)->where('bdc_building_id', $building)->orderBy('created_at', 'DESC')->paginate($perPage);
        return $bills;
    }

    public function getWaitForConfirmOfBuilding($perPage, $building)
    {
        $monthNow = Carbon::now()->month;
        $bills = $this->model->whereMonth('created_at', $monthNow)
            ->where(['bdc_building_id' => $building, 'status' => self::WAIT_FOR_CONFIRM])
            ->orderBy('created_at', 'DESC')
            ->paginate($perPage);
        return $bills;
    }

    public function findCustomerOfBill($billId)
    {
    }

    public function findBuildingApartmentIdV2($buildingId, $apartmentId)
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        return $this->model
            ->with([
                'debitDetail' => function ($query) {
                    $query->where('version', '=', 0);
                }
            ])
            ->where(['bdc_building_id' => $buildingId, 'bdc_apartment_id' => $apartmentId, 'status' => self::PAYING])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
    }

    public function findBuildingApartmentId($buildingId, $apartmentId)
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        return $this->model
            ->with([
                'debitDetail' => function ($query) {
                    $query->where('version', '=', 0);
                }
            ])
            ->where(['bdc_building_id' => $buildingId, 'bdc_apartment_id' => $apartmentId])
            ->where('status' ,'>=', self::WAIT_TO_SEND)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
    }

    public function findBillCode($buildingId, $billCode)
    {
        return $this->model
            ->where(['bdc_building_id' => $buildingId, 'bill_code' => $billCode])
            ->first();
    }
    public function findBillCode_v1($billCode)
    {
        return $this->model
            ->where(['bill_code' => $billCode])
            ->first();
    }

    public function findBillCodeServiceId($buildingId, $billCode, $apartmentServicePriceId)
    {
        return $this->model
            ->with([
                'debitDetail' => function ($query) use ($apartmentServicePriceId) {
                    $query->where(['bdc_apartment_service_price_id' => $apartmentServicePriceId]);
                }
            ])
            ->where(['bdc_building_id' => $buildingId, 'bill_code' => $billCode])
            ->first();
    }

    public static function findMaxVersionPaidByBill($billId)
    {
        return DB::select(DB::raw('SELECT `bdc_building_id`, `bdc_apartment_id`, SUM(paid) AS total_paid FROM (
            SELECT bdc_building_id, bdc_apartment_id, MAX(`version`), paid FROM `bdc_debit_detail` 
            WHERE `bdc_debit_detail`.`bdc_bill_id` = :billId AND `bdc_debit_detail`.`deleted_at` IS NULL 
            GROUP BY `bdc_building_id`, `bdc_apartment_id`, paid
        ) AS tb1 GROUP BY `bdc_building_id`, `bdc_apartment_id`'), ['billId' => $billId]);
    }

    public function export($buildingId, $request = null)
    { 
        $bill = $this->model->join('bdc_apartments', 'bdc_apartments.id', '=', 'bdc_bills.bdc_apartment_id')->where('bdc_building_id',$buildingId)->where(function($query) use($request,$buildingId){
            if (isset($request['from_date']) && $request['from_date'] != null && isset($request['to_date']) && $request['to_date'] != null) {
                $from_date = Carbon::parse($request['from_date'])->format('Y-m-d');
                $to_date   = Carbon::parse($request['to_date'])->format('Y-m-d');
                $query->whereDate('bdc_bills.created_at', '>=', $from_date);
                $query->whereDate('bdc_bills.created_at', '<=', $to_date);
            }
            if (isset($request['bdc_apartment_id']) && $request['bdc_apartment_id'] != null) {
                $bdc_apartment_id = $request['bdc_apartment_id'];
                $query->where('bdc_apartment_id', $bdc_apartment_id);
            }
            if(isset($request['ip_place_id']) && $request['ip_place_id'] != null){
                $query->whereHas('apartment', function (Builder $query) use ($request) {
                    $ip_place_id = $request['ip_place_id'];
                        $query->where('building_place_id', $ip_place_id);
                });
            }
            if(isset($request['status']) && $request['status'] != null){
                $status = $request['status'];
                $check_dateline = Carbon::now()->format('Y-m-d');
                if($status == 'qua_han'){
                    $query->where('bdc_bills.status', '>', -2);
                    $query->whereDate('deadline', '<', $check_dateline);
                    $billIds =  DebitDetail::where(['bdc_building_id'=>$buildingId])
                                ->whereHas('bill',function($query){
                                    $query->where('bdc_bills.status','>',-2);
                                })
                                ->select('bdc_bill_id',DB::raw('sum(sumery) - sum(paid) as total_sum'))->groupBy('bdc_bill_id')->get();
                                $billIds =  $billIds->where('total_sum','>',0)->pluck('bdc_bill_id')->toArray();   
                                if(count($billIds) > 0){
                                    $billIds = join(",",$billIds);
                                    $query->whereRaw("bdc_bills.id in ($billIds)");
                                }else{
                                    $query->whereRaw("bdc_bills.id in (0)");
                                }  
                }
                else if($status == 'da_thanh_toan'){
                    $query->where('bdc_bills.status', '>', -2);
                    $billIds =  DebitDetail::where(['bdc_building_id'=>$buildingId])
                                ->whereHas('bill',function($query){
                                    $query->where('bdc_bills.status','>',-2);
                                })
                                ->select('bdc_bill_id',DB::raw('sum(sumery) - sum(paid) as total_sum'))->groupBy('bdc_bill_id')->get();
                                $billIds =  $billIds->where('total_sum','=',0)->pluck('bdc_bill_id')->toArray();   
                                if(count($billIds) > 0){
                                    $billIds = join(",",$billIds);
                                    $query->whereRaw("bdc_bills.id in ($billIds)");
                                }else{
                                    $query->whereRaw("bdc_bills.id in (0)");
                                }  

                }else if($status == 'can_thong_bao'){
                    $query->where('bdc_bills.status', '=', -2);
                    $billIds =  DebitDetail::where(['bdc_building_id'=>$buildingId])
                                ->whereHas('bill',function($query){
                                    $query->where('bdc_bills.status','=',-2);
                                })
                                ->select('bdc_bill_id',DB::raw('sum(sumery) - sum(paid) as total_sum'))->groupBy('bdc_bill_id')->get();
                                $billIds =  $billIds->where('total_sum','>',0)->pluck('bdc_bill_id')->toArray();    
                                if(count($billIds) > 0){
                                    $billIds = join(",",$billIds);
                                    $query->whereRaw("bdc_bills.id in ($billIds)");
                                }else{
                                    $query->whereRaw("bdc_bills.id in (0)");
                                }

                }else if($status == 'dang_thanh_toan'){
                    $query->where('bdc_bills.status', '>', -2);
                    $query->whereDate('deadline', '>=', $check_dateline);
                    $billIds =  DebitDetail::where(['bdc_building_id'=>$buildingId])
                                ->whereHas('bill',function($query){
                                    $query->where('bdc_bills.status','>',-2);
                                })
                                ->select('bdc_bill_id',DB::raw('sum(sumery) - sum(paid) as total_sum'))->groupBy('bdc_bill_id')->get();
                        $billIds =  $billIds->where('total_sum','<>',0)->pluck('bdc_bill_id')->toArray(); 
                        if(count($billIds) > 0){
                            $billIds = join(",",$billIds);
                            $query->whereRaw("bdc_bills.id in ($billIds)");
                        }else{
                            $query->whereRaw("bdc_bills.id in (0)");
                        }
                }
                else{
                    $query->where('bdc_bills.status', '=', $status);
                }
               
            }else{
                $query->where('bdc_bills.status', '>=', -3);
            }
        })->whereHas('debitDetailV2')->whereNull('bdc_apartments.deleted_at')->orderBy('bdc_apartments.building_place_id')->orderBy('bdc_apartments.name')->orderBy('bdc_bills.updated_at', 'desc')->orderBy('bdc_bills.status', 'desc')->select(['bdc_bills.*'])->get();
        $result = Excel::create('Hóa đơn tổng hợp', function ($excel) use ($bill) {
            $excel->setTitle('Hóa đơn tổng hợp');
            $excel->sheet('Hóa đơn tổng hợp', function ($sheet) use ($bill) {
                $bills = [];
                $now =  \Carbon\Carbon::now()->format('d-m-Y');
                $sheet->setColumnFormat([
                    'F2' => \PHPExcel_Style_NumberFormat::FORMAT_NUMBER,
                    'G2' => \PHPExcel_Style_NumberFormat::FORMAT_NUMBER,
                    'H2' => \PHPExcel_Style_NumberFormat::FORMAT_NUMBER,
                ]);
                $row = 1;
                $sheet->row($row, [
                    'STT',
                    'Mã hóa đơn',
                    'Kỳ HĐ',
                    'Căn hộ',
                    'Hạn TT',
                    'Tổng phát sinh',
                    'Giảm trừ',
                    'Thành tiền',
                    'Đã thanh toán',
                    'Còn nợ',
                    'Ngày tạo',
                    'Trạng thái',
                ]);
                foreach ($bill as $key => $value) {
                    $row++;
                    $debits = BdcV2DebitDetailDebitDetailRepository::findByBillId($value->id);
                    $sumCost = 0;
                    $sumPaid = 0;
                    $sumDiscount = 0;
                    foreach ($debits as $value_1) {
                        $sumCost += (int) $value_1->sumery;
                        $sumPaid += (int) $value_1->paid;
                        $sumDiscount += (int) $value_1->discount;
                    }
                    if ($value->apartment) {
                        $aprtment_name = $value->apartment->name;
                    } else {
                        $aprtment_name = '';
                    }
                    $no = $sumCost - $sumPaid;
                    $sheet->row($row, [
                        ($key + 1),
                        $value->bill_code,
                        $value->cycle_name,
                        $aprtment_name,
                        date('d-m-Y', strtotime(@$value->deadline)),
                        $sumCost + $sumDiscount,
                        $sumDiscount,
                        $sumCost,
                        $sumPaid,
                        $no,
                        date('d/m/Y', strtotime(@$value->created_at)),
                        $this->getStatusBill($value),
                    ]);
                }
            });
        })->store('xlsx',storage_path('exports/'));
        ob_end_clean();
        $file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
        return response()->download($file)->deleteFileAfterSend(true);
             
    }

    public function filterBill($buildingId, $request, $perPage)
    {
        $response = $this->model->whereHas('building', function (Builder $query) use ($buildingId) {
            if (isset($buildingId)) {
                $query->where('id', '=', $buildingId);
            }
        })->whereHas('apartment', function (Builder $query) use ($request) {
            if (isset($request['bdc_apartment_id'])) {
                $query->where('id', '=', $request['bdc_apartment_id']);
            }
            if(isset($request['ip_place_id'])){
                $query->where('building_place_id', $request['ip_place_id']);
            }
        })->where(function ($query) use ($request) {
            if (isset($request['status'])) {
                $query->where('status', '=', $request['status']);
            }
        })->where(function ($query) use ($request) {
            if (isset($request['from_date']) && isset($request['to_date'])) {
                $from_date = Carbon::parse($request['from_date'])->format('Y-m-d');
                $to_date   = Carbon::parse($request['to_date'])->format('Y-m-d');
                $query->whereDate('confirm_date', '>=', $from_date);
                $query->whereDate('confirm_date', '<=', $to_date);
            }
        })->orderBy('confirm_date', 'DESC')->paginate($perPage);
        return $response;
    }

    public function filterBillv2($buildingId, $request)
    {
        $sql="SELECT `bdc_bills`.*,total_paid,total_sumery,`bdc_apartments`.`name` from `bdc_bills` INNER JOIN (        
            SELECT `bdc_bill_id`,`bdc_building_id`, SUM(paid + paid_v3) AS total_paid  FROM (
                        SELECT `bdc_bill_id`,`bdc_building_id`,MAX(`version`), paid, paid_v3 FROM `bdc_debit_detail` 
                            WHERE `bdc_debit_detail`.`bdc_bill_id` in (SELECT `id` from `bdc_bills` WHERE `bdc_bills`.`deleted_at` IS NULL ) AND `bdc_debit_detail`.`bdc_building_id` = $buildingId AND `bdc_debit_detail`.`deleted_at` IS NULL 
                        GROUP BY `bdc_building_id`, `paid`,`bdc_bill_id`
                    ) AS tb1 GROUP BY `bdc_building_id`, `bdc_bill_id`) AS tb2 on `bdc_bills`.`id` = tb2.`bdc_bill_id`
                    INNER JOIN (SELECT `bdc_bill_id`,`bdc_building_id`,  SUM(sumery) AS total_sumery FROM (
                        SELECT `bdc_bill_id`,`bdc_building_id`,MAX(`version`), sumery FROM `bdc_debit_detail` 
                            WHERE `bdc_debit_detail`.`bdc_bill_id` in (SELECT `id` from `bdc_bills` WHERE `bdc_bills`.`deleted_at` IS NULL) AND `bdc_debit_detail`.`bdc_building_id` = $buildingId AND `bdc_debit_detail`.`deleted_at` IS NULL 
                        GROUP BY `bdc_building_id`, `sumery`,`bdc_bill_id`
                    ) AS tb3 GROUP BY `bdc_building_id`, `bdc_bill_id`) AS tb4 ON `bdc_bills`.`id` = tb4.`bdc_bill_id` INNER JOIN `bdc_apartments` ON `bdc_apartments`.`id` = `bdc_bills`.`bdc_apartment_id`  WHERE `bdc_bills`.`deleted_at` IS NULL ";
                    if (isset($request['from_date']) && isset($request['to_date'])) {
                        $from_date = Carbon::parse($request['from_date'])->format('Y-m-d');
                        $to_date   = Carbon::parse($request['to_date'])->format('Y-m-d');
                        $sql.="AND `bdc_bills`.`confirm_date` >= '$from_date' AND `bdc_bills`.`confirm_date` <= '$to_date'";
                    }
                    if (isset($request['bdc_apartment_id']) && $request['bdc_apartment_id'] != null) {
                        $bdc_apartment_id = $request['bdc_apartment_id'];
                        $sql.="AND `bdc_bills`.`bdc_apartment_id` = $bdc_apartment_id ";
                    }
                    if(isset($request['ip_place_id']) && $request['ip_place_id'] != null){
                        $ip_place_id = $request['ip_place_id'];
                        $sql.="AND `bdc_bills`.`bdc_apartment_id` = $ip_place_id ";
                    }
                    if(isset($request['status']) && $request['status'] != null){
                        $status = $request['status'];
                        $check_dateline = Carbon::now()->format('Y-m-d');
                        if($status == 'qua_han'){
                            $sql.="AND `bdc_bills`.`status` >= -2 AND `bdc_bills`.`deadline` < '$check_dateline' AND `tb2`.`total_paid` < `tb4`.`total_sumery` ";
                        }else if($status == 'da_thanh_toan'){
                            $sql.="AND `bdc_bills`.`status` >= -2 AND `tb2`.`total_paid` = `tb4`.`total_sumery` ";
                        }else if($status == 'can_thong_bao'){
                            $sql.="AND `bdc_bills`.`status` = -2 AND `tb2`.`total_paid` <> `tb4`.`total_sumery` ";
                        }
                        else{
                            $sql.="AND `bdc_bills`.`status` = $status";
                        }
                       
                    }
                    $sql.=" ORDER BY `bdc_bills`.`confirm_date` DESC";
        return  DB::select(DB::raw($sql));
    }
    public function getBillv2($buildingId, $request)
    {
        $bill = $this->model->join('bdc_apartments', 'bdc_apartments.id', '=', 'bdc_bills.bdc_apartment_id')->where('bdc_building_id',$buildingId)->where(function($query) use($request,$buildingId){
            if (isset($request['bill_code']) && $request['bill_code'] != null) {
                $query->where('bill_code', '=', $request['bill_code']);
            }
            if (isset($request['from_date']) && isset($request['to_date'])) {
                $from_date = Carbon::parse($request['from_date'])->format('Y-m-d');
                $to_date   = Carbon::parse($request['to_date'])->format('Y-m-d');
                $query->whereDate('bdc_bills.created_at', '>=', $from_date);
                $query->whereDate('bdc_bills.created_at', '<=', $to_date);
            }
            if (isset($request['bdc_apartment_id']) && $request['bdc_apartment_id'] != null) {
                $bdc_apartment_id = $request['bdc_apartment_id'];
                $query->where('bdc_apartment_id', $bdc_apartment_id);
            }
            if (isset($request['cycle_name']) && $request['cycle_name'] != null) {
                $cycle_name = $request['cycle_name'];
                $query->where('cycle_name', $cycle_name);
            }
            if(isset($request['ip_place_id']) && $request['ip_place_id'] != null){
               
                $query->whereHas('apartment', function (Builder $query) use ($request) {
                    $ip_place_id = $request['ip_place_id'];
                        $query->where('building_place_id', $ip_place_id);
                });
            }
            if(isset($request['status']) && $request['status'] != null){
                $status = $request['status'];
                $check_dateline = Carbon::now()->format('Y-m-d');
                if($status == 'qua_han'){
                    $query->where('bdc_bills.status', '>', -2);
                    $query->whereDate('deadline', '<=', $check_dateline);
                    $billIds =  DebitDetail::where(['bdc_building_id'=>$buildingId])
                                ->whereHas('bill',function($query){
                                    $query->where('bdc_bills.status','>',-2);
                                })
                                ->select('bdc_bill_id',DB::raw('sum(sumery) - sum(paid) as total_sum'))->groupBy('bdc_bill_id')->get();
                                $billIds =  $billIds->where('total_sum','>',0)->pluck('bdc_bill_id')->toArray(); 
                                if(count($billIds) > 0){
                                    $billIds = join(",",$billIds);
                                    $query->whereRaw("bdc_bills.id in ($billIds)");
                                }else{
                                    $query->whereRaw("bdc_bills.id in (0)");
                                }  
                              
                }
                else if($status == 'da_thanh_toan'){
                    $query->where('bdc_bills.status', '>', -2);
                    $billIds =  DebitDetail::where(['bdc_building_id'=>$buildingId])
                                ->whereHas('bill',function($query){
                                    $query->where('bdc_bills.status','>',-2);
                                })
                                ->select('bdc_bill_id',DB::raw('sum(sumery) - sum(paid) as total_sum'))->groupBy('bdc_bill_id')->get();
                                $billIds =  $billIds->where('total_sum','=',0)->pluck('bdc_bill_id')->toArray();   
                                if(count($billIds) > 0){
                                    $billIds = join(",",$billIds);
                                    $query->whereRaw("bdc_bills.id in ($billIds)");
                                }else{
                                    $query->whereRaw("bdc_bills.id in (0)");
                                }  

                }else if($status == 'can_thong_bao'){
                    $query->where('bdc_bills.status', '=', -2);
                    //$query->whereDate('deadline', '>=', $check_dateline);
                    $billIds =  DebitDetail::where(['bdc_building_id'=>$buildingId])
                                ->whereHas('bill',function($query){
                                    $query->where('bdc_bills.status','=',-2);
                                })
                                ->select('bdc_bill_id',DB::raw('sum(sumery) - sum(paid) as total_sum'))->groupBy('bdc_bill_id')->get();
                                $billIds =  $billIds->where('total_sum','>',0)->pluck('bdc_bill_id')->toArray();    
                                if(count($billIds) > 0){
                                    $billIds = join(",",$billIds);
                                    $query->whereRaw("bdc_bills.id in ($billIds)");
                                }else{
                                    $query->whereRaw("bdc_bills.id in (0)");
                                }  
                }else if($status == 'dang_thanh_toan'){
                    $query->where('bdc_bills.status', '>', -2);
                    $query->whereDate('deadline', '>=', $check_dateline);
                    $billIds =  DebitDetail::where(['bdc_building_id'=>$buildingId])
                                ->whereHas('bill',function($query){
                                    $query->where('bdc_bills.status','>',-2);
                                })
                                ->select('bdc_bill_id',DB::raw('sum(sumery) - sum(paid) as total_sum'))->groupBy('bdc_bill_id')->get();
                        $billIds =  $billIds->where('total_sum','<>',0)->pluck('bdc_bill_id')->toArray(); 
                        if(count($billIds) > 0){
                            $billIds = join(",",$billIds);
                            $query->whereRaw("bdc_bills.id in ($billIds)");
                        }else{
                            $query->whereRaw("bdc_bills.id in (0)");
                        }  
                }
                else{
                    $query->where('bdc_bills.status', '>=', $status);
                }
               
            }else{
                $query->where('bdc_bills.status', '>=', -3);
            }
        });
        return $bill->whereHas('debitDetailV2')->whereNull('bdc_apartments.deleted_at')->orderBy('bdc_apartments.building_place_id')->orderBy('bdc_apartments.name')->orderBy('bdc_bills.updated_at', 'desc')->orderBy('bdc_bills.status', 'desc')->select(['bdc_bills.*']);
    }
    public function filterBillv3($buildingId, $request)
    {
        $sql="SELECT `bdc_bills`.*,total_paid,total_sumery,`bdc_apartments`.`name` from `bdc_bills` INNER JOIN (        
            SELECT `bdc_bill_id`,`bdc_building_id`, SUM(paid) AS total_paid  FROM (
                        SELECT `bdc_bill_id`,`bdc_building_id`,MAX(`version`), paid FROM `bdc_debit_detail` 
                            WHERE `bdc_debit_detail`.`bdc_bill_id` in (SELECT `id` from `bdc_bills` ) AND `bdc_debit_detail`.`bdc_building_id` = $buildingId 
                        GROUP BY `bdc_building_id`, `paid`,`bdc_bill_id`
                    ) AS tb1 GROUP BY `bdc_building_id`, `bdc_bill_id`) AS tb2 on `bdc_bills`.`id` = tb2.`bdc_bill_id`
                    INNER JOIN (SELECT `bdc_bill_id`,`bdc_building_id`,  SUM(sumery) AS total_sumery FROM (
                        SELECT `bdc_bill_id`,`bdc_building_id`,MAX(`version`), sumery FROM `bdc_debit_detail` 
                            WHERE `bdc_debit_detail`.`bdc_bill_id` in (SELECT `id` from `bdc_bills` ) AND `bdc_debit_detail`.`bdc_building_id` = $buildingId 
                        GROUP BY `bdc_building_id`, `sumery`,`bdc_bill_id`
                    ) AS tb3 GROUP BY `bdc_building_id`, `bdc_bill_id`) AS tb4 ON `bdc_bills`.`id` = tb4.`bdc_bill_id` INNER JOIN `bdc_apartments` ON `bdc_apartments`.`id` = `bdc_bills`.`bdc_apartment_id` ";
                    if (isset($request['bill_code']) && isset($request['bill_code'])) {
                        $bill_code = $request['bill_code'];
                        $sql.="AND `bdc_bills`.`bill_code` = '$bill_code' ";
                    }
                    if (isset($request['from_date']) && isset($request['to_date'])) {
                        $from_date = Carbon::parse($request['from_date'])->format('Y-m-d');
                        $to_date   = Carbon::parse($request['to_date'])->format('Y-m-d');
                        $sql.="AND `bdc_bills`.`confirm_date` >= '$from_date' AND `bdc_bills`.`confirm_date` <= '$to_date'";
                    }
                    if (isset($request['bdc_apartment_id']) && $request['bdc_apartment_id'] != null) {
                        $bdc_apartment_id = $request['bdc_apartment_id'];
                        $sql.="AND `bdc_bills`.`bdc_apartment_id` = $bdc_apartment_id ";
                    }
                    if(isset($request['ip_place_id']) && $request['ip_place_id'] != null){
                        $ip_place_id = $request['ip_place_id'];
                        $sql.="AND `bdc_bills`.`bdc_apartment_id` = $ip_place_id ";
                    }
                    if(isset($request['status']) && $request['status'] != null){
                        $status = $request['status'];
                        $check_dateline = Carbon::now()->format('Y-m-d');
                        if($status == 'qua_han'){
                            $sql.="AND `bdc_bills`.`status` >= -2 AND `bdc_bills`.`deadline` < '$check_dateline' AND `tb2`.`total_paid` < `tb4`.`total_sumery` ";
                        }else if($status == 'da_thanh_toan'){
                            $sql.="AND `bdc_bills`.`status` >= -2 AND `tb2`.`total_paid` = `tb4`.`total_sumery` ";
                        }else if($status == 'can_thong_bao'){
                            $sql.="AND `bdc_bills`.`status` = -2 AND `tb2`.`total_paid` <> `tb4`.`total_sumery` ";
                        }
                        else{
                            $sql.="AND `bdc_bills`.`status` = $status";
                        }
                       
                    }
                    $sql.=" ORDER BY `bdc_bills`.`updated_at` DESC";
        return  DB::select(DB::raw($sql));
    }

    public function filterWaitForConfirm($buildingId, $request, $perPage)
    {
        $response = $this->model->whereHas('building', function (Builder $query) use ($buildingId) {
                                    if (isset($buildingId)) {
                                        $query->where('id', '=', $buildingId);
                                    }
                                })
                                ->whereHas('debitDetailV2')
                                ->whereHas('apartment', function (Builder $query) use ($request) {
                                    if (isset($request['bdc_apartment_id'])  && $request['bdc_apartment_id'] != null) {
                                        $query->where('id', '=', $request['bdc_apartment_id']);
                                    }
                                    if(isset($request['ip_place_id'])){
                                        $query->where('building_place_id', $request['ip_place_id']);
                                    }
                                })->where(function ($query) use ($request) {
                                    if (isset($request['from_date']) && $request['from_date'] != null && isset($request['to_date']) && $request['to_date'] != null) {

                                        $from_date = Carbon::parse($request['from_date'])->format('Y-m-d');
                                        $to_date   = Carbon::parse($request['to_date'])->format('Y-m-d');

                                        $query->whereDate('created_at', '>=', $from_date);
                                        $query->whereDate('created_at', '<=', $to_date);
                                    }
                                    if (isset($request['bill_code']) && $request['bill_code'] != null) {
                                        $query->where('bill_code', '=', $request['bill_code']);
                                    }
                                })
                                ->where('status', self::WAIT_FOR_CONFIRM)
                                ->orderBy('created_at', 'DESC')->paginate($perPage);
        return $response;
    }
    public function filterWaitForConfirmV2($buildingId, $request, $perPage)
    {
        $response = $this->model->whereHas('building', function (Builder $query) use ($buildingId) {
            if (isset($buildingId)) {
                $query->where('id', '=', $buildingId);
            }
        })->whereHas('apartment', function (Builder $query) use ($request) {
            if (isset($request['bdc_apartment_id'])  && $request['bdc_apartment_id'] != null) {
                $query->where('id', '=', $request['bdc_apartment_id']);
            }
            if(isset($request['ip_place_id'])){
                $query->where('building_place_id', $request['ip_place_id']);
            }
        })->where(function ($query) use ($request) {
            if (isset($request['from_date']) && $request['from_date'] != null && isset($request['to_date']) && $request['to_date'] != null) {
                $from_date = Carbon::parse($request['from_date'])->format('Y-m-d');
                $to_date   = Carbon::parse($request['to_date'])->format('Y-m-d');

                $query->whereDate('created_at', '>=', $from_date);
                $query->whereDate('created_at', '<=', $to_date);
            }
            if (isset($request['bill_code']) && $request['bill_code'] != null) {
                $query->where('bill_code', '=', $request['bill_code']);
            }
            if (isset($request['status']) && $request['status'] != null) {
                $query->where('status', '=', $request['status']);
            }
        })
            ->withTrashed()
            ->orderBy('created_at', 'DESC')->paginate($perPage);
        return $response;
    }

    public function filterWaitToSend($buildingId, $cycle_names, $request, $perPage)
    {
        $response = $this->model->whereHas('building', function (Builder $query) use ($buildingId) {
            if (isset($buildingId)) {
                $query->where('id', '=', $buildingId);
            }
        })->whereHas('apartment', function (Builder $query) use ($request) {
            if (isset($request['bdc_apartment_id']) && $request['bdc_apartment_id'] != null) {
                $query->where('id', '=', $request['bdc_apartment_id']);
            }
            if(isset($request['ip_place_id'])){
                $query->where('building_place_id', $request['ip_place_id']);
            }
        })->where(function ($query) use ($request,$cycle_names) {
            if (isset($request['from_date']) && $request['from_date'] != null && isset($request['to_date']) && $request['to_date'] != null) {

                $from_date = Carbon::parse($request['from_date'])->format('Y-m-d');
                $to_date   = Carbon::parse($request['to_date'])->format('Y-m-d');

                $query->whereDate('created_at', '>=', $from_date);
                $query->whereDate('created_at', '<=', $to_date);
            }
            if (isset($request['bill_code']) && $request['bill_code'] != null) {
                $query->where('bill_code', '=', $request['bill_code']);
            }
            if ($cycle_names != 'ky_bang_ke') {
                $query->where('cycle_name',$cycle_names);
            }
        })
        ->whereHas('debitDetailV2')
        ->where(function ($query) {
            $query->where('status', self::WAIT_TO_SEND);
        })
        ->orderBy('created_at', 'DESC')->paginate($perPage);
        return $response;
    }

    public function filterPay($buildingId, $request, $perPage)
    {
        $response = $this->model->whereHas('building', function (Builder $query) use ($buildingId) {
            if (isset($buildingId)) {
                $query->where('id', '=', $buildingId);
            }
        })->whereHas('apartment', function (Builder $query) use ($request) {
            if (isset($request['bdc_apartment_id']) && $request['bdc_apartment_id'] != null) {
                $query->where('id', '=', $request['bdc_apartment_id']);
            }
            if(isset($request['ip_place_id'])){
                $query->where('building_place_id', $request['ip_place_id']);
            }
        })->where(function ($query) use ($request) {
            if (isset($request['from_date']) && $request['from_date'] != null && isset($request['to_date']) && $request['to_date'] != null) {
                $from_date = Carbon::parse($request['from_date'])->format('Y-m-d');
                $to_date   = Carbon::parse($request['to_date'])->format('Y-m-d');

                $query->whereDate('created_at', '>=', $from_date);
                $query->whereDate('created_at', '<=', $to_date);
            }
            if (isset($request['bill_code']) && $request['bill_code'] != null) {
                $query->where('bill_code', '=', $request['bill_code']);
            }
        })
            ->where('status', '>=', self::WAIT_TO_SEND)
            ->orderBy('created_at', 'DESC')->paginate($perPage);
        return $response;
    }

    public function filterBillExport($buildingId, $request)
    {
        $response = $this->model->whereHas('building', function (Builder $query) use ($buildingId) {
            if (isset($buildingId)) {
                $query->where('id', '=', $buildingId);
            }
        })->whereHas('apartment', function (Builder $query) use ($request) {
            if (isset($request['bdc_apartment_id'])) {
                $query->where('id', '=', $request['bdc_apartment_id']);
            }
        })->where(function ($query) use ($request) {
            if (isset($request['status'])) {
                $query->where('status', '=', $request['status']);
            }
        })->where(function ($query) use ($request) {
            if (isset($request['from_date']) && isset($request['to_date'])) {
                $query->whereDate('confirm_date', '>=', $request['from_date']);
                $query->whereDate('confirm_date', '<=', $request['to_date']);
            }
        })->get();

        $result = Excel::create('Hóa đơn tổng hợp', function ($excel) use ($response) {
            $excel->setTitle('Hóa đơn tổng hợp');
            $excel->sheet('Hóa đơn tổng hợp', function ($sheet) use ($response) {
                $bills = [];
                $now =  \Carbon\Carbon::now();
                $row = 1;
                $sheet->row($row, [
                    'STT',
                    'Mã hóa đơn',
                    'Kỳ HĐ',
                    'Căn hộ',
                    'Hạn TT',
                    'Tổng giá trị',
                    'Đã thanh toán',
                    'Còn nợ',
                    'Ngày tạo',
                    'Trạng thái',
                ]);
                foreach ($response as $key => $value) {
                    $row++;
                    $find_debit = BdcV2DebitDetailDebitDetailRepository::findByBillId($value->id);
                    $sumery_price_after_discount = 0;
                    $sumPaid = 0;
                    $discount = 0;
                    foreach($find_debit as $value_1){
                        $sumery_price_after_discount += (int) $value_1->sumery ;
                        $sumPaid += (int) $value_1->paid;
                        $discount += (int) $value_1->discount;
                    }
                    if ($value->apartment) {
                        $aprtment_name = $value->apartment->name;
                    } else {
                        $aprtment_name = '';
                    }
                    $sheet->row($row, [
                        ($key + 1),
                        $value->bill_code,
                        $value->cycle_name,
                        $aprtment_name,
                        date('d-m-Y', strtotime(@$value->deadline)),
                        $sumery_price_after_discount,
                        $sumPaid,
                        $sumery_price_after_discount - $sumPaid,
                        date('d/m/Y', strtotime(@$value->created_at)),
                        $this->getStatusBill($value),
                    ]);
                }
            });
        })->store('xlsx',storage_path('exports/'));
        ob_end_clean();
        $file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
        return response()->download($file)->deleteFileAfterSend(true);
             
    }
    public function filterBillExportBangKeKhachHang($buildingId, $request)
    {
        $response = $this->getBillv2($buildingId, $request)->get();

        $result = Excel::create('Hóa đơn tổng hợp', function ($excel) use ($response) {
            $excel->setTitle('Hóa đơn tổng hợp');
            $excel->sheet('Hóa đơn tổng hợp', function ($sheet) use ($response) {
                $row = 1;
                $sheet->row($row, [
                    'STT',
                    'Mã hóa đơn',
                    'Kỳ HĐ',
                    'Căn hộ',
                    'Hạn TT',
                    'Tổng giá trị',
                    'Giảm trừ',
                    'Thành tiền',
                    'Đã thanh toán',
                    'Còn nợ',
                    'Ngày duyệt',
                    'Trạng thái',
                    'Người tạo',
                    'Người duyệt',
                    'Người gửi',
                ]);
                foreach ($response as $key => $value) {
                    $row++;
                    $debits = BdcV2DebitDetailDebitDetailRepository::findByBillId($value->id);
                    $sumCost = 0;
                    $sumPaid = 0;
                    $sumDiscount = 0;
                    foreach ($debits as $value_1) {
                        $sumCost += (int) $value_1->sumery;
                        $sumPaid += (int) $value_1->paid;
                        $sumDiscount += (int) $value_1->discount;
                    }
                    $sheet->row($row, [
                        ($key + 1),
                        $value->bill_code,
                        $value->cycle_name,
                        $value->bdc_apartment_id ? Apartments::get_detail_apartment_by_apartment_id($value->bdc_apartment_id)->name : null,
                        date('d-m-Y', strtotime(@$value->deadline)),
                        $sumCost+$sumDiscount,
                        $sumDiscount,
                        $sumCost,
                        $sumPaid,
                        $sumCost-$sumPaid,
                        date('d/m/Y', strtotime(@$value->confirm_date)),
                        $this->getStatusBill($value),
                        $value->user_id ? Users::find($value->user_id)->email??'' :'',
                        $value->approved_id ? Users::find($value->approved_id)->email??'' :'',
                        $value->sender_id ? Users::find($value->sender_id)->email??'' :'',
                    ]);
                }
            });
        })->store('xlsx',storage_path('exports/'));
        ob_end_clean();
        $file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
        return response()->download($file)->deleteFileAfterSend(true);
             
    }

    public function findBillById($id)
    {
        return $this->model->where('id', $id)->first();
    }

    public static function getBillById($id, $useCache = true)
    {
        $keyCache = "getBillById_" . $id;
        if(!$useCache) Cache::forget($keyCache);
        return Cache::remember($keyCache, 5, function () use ($id) {
            $rs = \App\Models\BdcBills\Bills::where('id', $id)->first();
            if (!$rs) return null;
            return (object) $rs->toArray();
        });
    }

    public function getBill($buildingId)
    {
        $monthNow = Carbon::now()->month;
        $bills = $this->model->whereMonth('created_at', $monthNow)->where('bdc_building_id', $buildingId)->get();
        return $bills;
    }

    public function changeMultiStatus($ids)
    {
        $response['responseStatusNumber'] = FAIL;
        $response['responseStatusText'] = '';
        $status = $this->model->whereIn('id', $ids)->get()->pluck('status')->toArray();
        $getStatus = $this->model->whereIn('id', $ids)->first();
        if (count(array_unique($status)) == 1) {
            switch ($getStatus->status) {
                case -3:
                    $response['responseStatusText'] = 'Xác nhận ';
                    $response['responseStatusNumber'] = -3;
                    break;
                case -2:
                    $response['responseStatusText'] = 'Gửi thông báo';
                    $response['responseStatusNumber'] = -2;
                    break;
            }
        } else {
            $response['responseStatusText'] = 'Không thực hiện';
            $response['responseStatusNumber'] = FAIL;
        }
        return $response;
    }

    public function postChangeMultiStatus($ids, $request = null)
    {
       
        $getStatus = $this->model->whereIn('id', $ids)->get();
        foreach ($getStatus as $bill) {
            $debitdetal =  BdcV2DebitDetailDebitDetailRepository::findByBillId($bill->id);
            if($debitdetal){
                foreach ($debitdetal as $key => $value) {
                    QueueRedis::setItemForQueue('add_queue_stat_payment_', [
                        "apartmentId" => $value->bdc_apartment_id,
                        "service_price_id" => $value->bdc_apartment_service_price_id,
                        "cycle_name" => $value->cycle_name,
                    ]);
                }
            }
            if ($bill->status == self::WAIT_FOR_CONFIRM) {
                $urlPdf = "admin/bill/detail/".$bill->bill_code;
                $this->model->where('id', $bill->id)->update(['status' => -2, 'confirm_date' => Carbon::now(), 'approved_id' => Auth::id(), 'url' => $urlPdf]);
            } else {
                $urlPdf = "admin/bill/detail/".$bill->bill_code;
                $this->model->where('id', $bill->id)->update(['status' => 1, 'confirm_date' => Carbon::now(), 'sender_id' => Auth::id(), 'url' => $urlPdf]);
                $bill = $this->model->where('id', $bill->id)->first();
                if ($bill) {
                    $apart = $bill->apartment;
                    $_customer = CustomersRespository::findResidentApartment($bill->bdc_apartment_id, $request->send_to == 0 ? 0 : null);
                    if ($_customer->count() > 0) {
                        // lấy tổng dư nợ cuối kỳ
                        $getServiceApartments = BdcV2DebitDetailDebitDetailRepository::getAllApartmentDetailLastTime4($bill->bdc_building_id, $bill->cycle_name, null, $bill->bdc_apartment_id, false, false, 100, 1);
                        $sumbill      = BdcV2DebitDetailDebitDetailRepository::sumByBillId($bill->id);
                        $total = ['email' => 1, 'app' => 1, 'sms' => 0];
                        $campain = Campain::updateOrCreateCampain("Bill: " . $bill->bill_code.' '.$bill->cycle_name, config('typeCampain.HOA_DON'), $bill->id, $total, $bill->bdc_building_id, 0, 0);
                        foreach ($_customer as $key_cus => $value_cus) {
                            $pubUserProfile = $value_cus ? PublicUsersProfileRespository::getInfoUserById($value_cus->user_info_id) : null;

                            $building = Building::get_detail_building_by_building_id($bill->bdc_building_id);
                            $data_noti_v2 = [
                                "message" => 'Trạng thái: chờ thanh toán',
                                "building_id" => $bill->bdc_building_id,
                                "title" => '[' . $building->name . "]_" . @$apart->name . ' hóa đơn kỳ tháng ' . @$bill->cycle_name,
                                "action_name" => self::BILL_NEW,
                                'type' => self::BILL_NEW,
                                'id' => $bill->id,
                                'avatar' => "avatar/system/01.png",
                                'app' => 'v2'
                            ];

                            $email = $pubUserProfile->email_contact;
                            $name = $pubUserProfile->full_name;
                            $pub_user_id = $pubUserProfile->user_id;
                            SendNotifyFCMService::setItemForQueueNotify(array_merge($data_noti_v2, ['user_id' => $pub_user_id, 'app_config' => @$building->template_mail == 'asahi' ? 'asahi' : 'cudan', 'campain_id' => $campain->id]));
                            // send mail
                            if (filter_var($pubUserProfile->email_contact, FILTER_VALIDATE_EMAIL)) {
                                $debitsTotal = null;
                                $view = null;
                                $sum_du_no_cuoi_ky = 0;
                                if ($getServiceApartments) {
                                    foreach ($getServiceApartments as $key => $value) {
                                        $servicePrice = ApartmentServicePriceRepository::getInfoServiceApartmentById($value->bdc_apartment_service_price_id);
                                        $Vehicles = null;
                                        if ($value->bdc_apartment_service_price_id == 0) {
                                            $service = (object) ["code_receipt" => "", "name" => "Tiền thừa"];
                                        } else {
                                            $service = Service::get_detail_bdc_service_by_bdc_service_id($servicePrice->bdc_service_id);
                                            if ($servicePrice->bdc_vehicle_id > 0) {
                                                $Vehicles = Vehicles::get_detail_vehicle_by_id($servicePrice->bdc_vehicle_id);
                                            }
                                        }
                                        $detail = [
                                            'ten_khach_hang' => @$pubUserProfile->full_name,
                                            'can_ho' =>  @$apart->name,
                                            'ma_san_pham' => @$apart->code,
                                            'ma_thu' =>  $service->code_receipt,
                                            'dich_vu' => isset($Vehicles) ? $service->name . ' - ' . $Vehicles->number  : $service->name,
                                            'dau_ky' =>  $value->cycle_name == $bill->cycle_name ? $value->before_cycle_name : $value->after_cycle_name,
                                            'trong_ky' => $value->cycle_name == $bill->cycle_name ? $value->sumery : 0,
                                            'thanh_toan' => $value->cycle_name == $bill->cycle_name ? $value->paid_by_cycle_name : 0,
                                            'cuoi_ky' => $value->after_cycle_name,
                                        ];
                                        $sum_du_no_cuoi_ky += $value->after_cycle_name;
                                        $debitsTotal[] = collect($detail);
                                    }
                                }
                                $base_url=((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http"). "://". @$_SERVER['HTTP_HOST'];
                                if ($debitsTotal) {
                                    $view = view('bill.v2._send_mail', compact('debitsTotal'))->render();
                                    $view = base64_encode($view);
                                    $cost = $sumbill->thanh_tien - $sumbill->thanh_toan;
                                    $url = isset($bill->url) ? $base_url . '/' . $bill->url . '?version=2' : "không có URL";
                                    $this->sendMailBill($email, @$name, @$cost, @$apart->name, @$bill->confirm_date, $url, $bill->bill_code, $bill->cycle_name, $bill->bdc_building_id, $sum_du_no_cuoi_ky, $bill->id, $campain, $view);
                                }
                            }
                        }
                    }
                }
            }
        }
        return $getStatus;
    }

    public function dashboardBill($building,$debitRepo)
    {
        $bills = $this->model->where('bdc_building_id', $building)->get();
        $outOfDate = array();
        $totalPaid = array();
        $totalBill = array();
        $now = Carbon::now();
        $month = Carbon::now()->month;
        foreach ($bills as $bill) {
            $findPaid = $debitRepo::findMaxVersionPaid($bill->id);
            $sumPaid = 0;
            foreach ($findPaid as $paid) {
                $sumPaid += (int) $paid->total_paid;
            }
            array_push($totalBill, $bill->cost);
            array_push($totalPaid, $sumPaid);
            if ($bill->status == 1 && date($bill->deadline) < $now && $bill->cost - $sumPaid > 0) {
                array_push($outOfDate, $bill->cost);
            }
        }
        $NewBorns = $this->model->where('bdc_building_id', $building)->whereMonth(
            'created_at',
            $month
        )->get()->pluck('cost')->toArray();
        $data['outOfDate'] = array_sum($outOfDate);
        $data['NewBorn'] = array_sum($NewBorns);
        $data['totalPaid'] = array_sum($totalPaid);
        $data['totalBill'] = array_sum($totalBill);
        return $data;
    }

    public function autoIncrementBillCode($config, $buildingId)
    {
        // $billCount = $this->model->count();
        $filterByKey = $config->getConfigbyKey('bill_code', $buildingId);
        $bill = collect(DB::select(DB::raw("SELECT MAX(RIGHT(`bill_code`, 7)) as bill_code FROM `bdc_bills` WHERE `bdc_building_id`=:buildingId AND `bill_code` LIKE '%$filterByKey%'"), ['buildingId' => $buildingId]))->first();

        if ($bill->bill_code == null) {
            $billCode = $filterByKey . "_0000001";
            return $billCode;
        }

        $numberBillCode = (int)$bill->bill_code;
        $numberBillCode = $numberBillCode + 1;
        $lengthNumberBillCode = strlen($numberBillCode);
        $idBillCode = substr('0000000',  0, 7 - $lengthNumberBillCode);
        $billCode = $filterByKey . "_" . $idBillCode . $numberBillCode;
        return $billCode;
    }

    public function autoIncrementReceiptCode($config, $buildingId)
    {
        // $billCount = $this->model->count();
        $filterByKey = $config->getConfigbyKey('receipt_code', $buildingId);
        $receipt = collect(DB::select(DB::raw("SELECT MAX(RIGHT(`receipt_code`, 7)) as receipt_code FROM `bdc_receipts` WHERE `bdc_building_id`=:buildingId AND `receipt_code` LIKE '%$filterByKey%'"), ['buildingId' => $buildingId]))->first();
        if ($receipt->receipt_code == null) {
            $receiptCode = $filterByKey . "_0000001";
            return $receiptCode;
        }

        $numberReceiptCode = (int)$receipt->receipt_code;
        $numberReceiptCode = $numberReceiptCode + 1;
        $lengthNumberReceiptCode = strlen($numberReceiptCode);
        $idReceiptCode = substr('0000000',  0, 7 - $lengthNumberReceiptCode);
        $receiptCode = $filterByKey . "_" . $idReceiptCode . $numberReceiptCode;
        return $receiptCode;
    }

    public function findBuildingApartmentIdWaitForConfirm($buildingId, $apartmentId)
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        return $this->model
            ->where(['bdc_building_id' => $buildingId, 'bdc_apartment_id' => $apartmentId, 'status' => self::WAIT_FOR_CONFIRM])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'DESC')
            ->first();
    }
    public function sendMailBill($email, $ten, $tongtien, $canho, $ngay, $linkpdf, $bill_code, $cycle_name, $building_id, $sum_du_no_cuoi_ky, $id, $campain, $detail)
    {
        $data = [
            'params' => [
                '@tenkhachhang' => $ten,
                '@tongtien' => number_format($tongtien),
                '@dunocuoiky' => number_format($sum_du_no_cuoi_ky),
                '@noidung' => $detail,
                '@canho' => $canho,
                '@ngay' => $ngay,
                '@linkpdf' => $linkpdf,
                '@billcode' => $bill_code,
                '@cyclename' => $cycle_name,
                '@url'=> ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http"). "://". @$_SERVER['HTTP_HOST']
            ],
            'cc' => $email,
            'building_id' => $building_id,
            'type' => 69,
            'status' => 'paid',
            'campain_id' => $campain->id
        ];
        Log::info('check_change_status_bill','check_1'.json_encode($data));
        try {
            ServiceSendMailV2::setItemForQueue($data);
            return;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function deleteBill($id, $debitRepo)
    {
        $bill = $this->model->where('id', $id)->first();
        $debit = $debitRepo->filterBillId($bill->id);
        if(!$debit->isEmpty()) {
            return false;
        } else {
            return $this->model->where('id', $id)->delete();
        }
    }

    public function getCurrentCycleName($cycleName, $apartmentId) {
        return $this->model->where(['cycle_name' => $cycleName, 'bdc_apartment_id' => $apartmentId])->get();
    }
    public function getStatusBill($bill) {

        $find_debit = BdcV2DebitDetailDebitDetailRepository::findByBillId($bill->id);
        $sumery_price_after_discount = 0;
        $sumPaid = 0;
        $now = Carbon::now();
        $deadline = Carbon::parse($bill->deadline);
        foreach($find_debit as $value){
            $sumery_price_after_discount += (int) $value->sumery ;
            $sumPaid += (int) $value->paid;
        }  
        $status = 'Chưa có';                              
        switch ($bill->status) {
            case -3:
                $status = 'Chờ xác nhận';
                break;
            case -2:
                $status = 'Chờ gửi';
                break;
            case 2:
                $status = 'Đã thanh toán';
                break;
            case 1:
                if($sumPaid >= $sumery_price_after_discount) {
                    $status = 'Đã thanh toán';
                }elseif ($deadline < $now && $sumery_price_after_discount > $sumPaid) {
                    $status = 'Quá hạn';
                }else {
                    $status = 'Chờ thanh toán';
                }
                break;
            default:
                $status = 'Chưa có';
                break;
        }
        return $status;
    }
}
