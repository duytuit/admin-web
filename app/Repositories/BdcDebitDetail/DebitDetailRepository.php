<?php

namespace App\Repositories\BdcDebitDetail;

use App\Exceptions\QueueRedis;
use App\Helpers\dBug;
use App\Models\Apartments\Apartments;
use App\Models\Apartments\V2\UserApartments;
use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Models\BdcBills\Bills;
use App\Models\BdcDebitDetail\DebitDetail;
use App\Models\BdcDebitLogs\DebitLogs;
use App\Models\CronJobManager\CronJobManager;
use App\Models\Service\Service;
use App\Repositories\BdcBills\BillRepository;
use App\Repositories\CronJobManager\CronJobManagerRepository;
use App\Repositories\Eloquent\Repository;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Maatwebsite\Excel\Facades\Excel;

const PAGE = 10;
const NO_STATUS = 0;
const FREE = 1;
const NO_FREE = 0;

class DebitDetailRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */

    const WAIT_FOR_CONFIRM = -3;
    const WAIT_TO_SEND = -2;
    const HAD_CONFIRM = -1;
    const PAYING = 1;
    const PAID = 2;
    const OUT_OF_DATE = 3;
    const PROCESS_AGAIN = 1;
    const NO_PROCESS_AGAIN = 0;

    function model()
    {
        return \App\Models\BdcDebitDetail\DebitDetail::class;
    }

    public function findByBuildingApartmentServiceId($buildingId, $apartmentId, $serviceId)
    {
        $currentDate = Carbon::now();
        $prevousDate = $currentDate->subDay(25);
        return $this->model->where([
            'bdc_building_id' => $buildingId,
            'bdc_apartment_id' => $apartmentId,
            'bdc_service_id' => $serviceId,
        ])
        ->whereDate('to_date', '<', $prevousDate)
        ->orderBy('to_date', 'desc')
        ->orderBy('version', 'desc')
        ->first();
    }

    public function findByBuildingApartmentId($buildingId, $apartmentId)
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        return DB::select(DB::raw('SELECT * FROM `bdc_debit_detail` as tb1 WHERE tb1.bdc_building_id=:buildingId AND tb1.bdc_apartment_id=:apartmentId 
            AND `tb1`.`to_date` >= :startDate AND `tb1`.`to_date` <= :endDate AND `tb1`.`deleted_at` IS NULL
            AND `tb1`.`version` = (SELECT MAX(tb2.version) FROM `bdc_debit_detail` as tb2 
                WHERE tb1.bdc_building_id=tb2.bdc_building_id AND tb1.bdc_apartment_id=tb2.bdc_apartment_id AND tb1.bdc_service_id=tb2.bdc_service_id 
                AND `tb1`.`to_date` >= :startDate2 AND `tb1`.`to_date` <= :endDate2) AND `tb2`.`deleted_at` IS NULL'), 
            ['buildingId' => $buildingId, 'apartmentId' => $apartmentId, 'startDate' => $startDate, 'endDate' => $endDate, 'startDate2' => $startDate, 'endDate2' => $endDate]);
    }


    public function findMaxVersion($buildingId, $apartmentId, $serviceId)
    {
        return $this->model->select(DB::raw('id, bdc_building_id, bdc_apartment_id, bdc_service_id, previous_owed, MAX(version) as version'))->where([
            'bdc_building_id' => $buildingId,
            'bdc_apartment_id' => $apartmentId,
            'bdc_service_id' => $serviceId,
        ])->groupBy('id', 'bdc_building_id', 'bdc_apartment_id', 'bdc_service_id', 'previous_owed')->get();
    }

    public function findMaxVersionByCurrentMonth($buildingId)
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        return $this->model->select(DB::raw('bdc_building_id, bdc_apartment_id, previous_owed, MAX(version) as version'))
        ->where([
            'bdc_building_id' => $buildingId,
        ])
        ->whereBetween('to_date', [$startDate, $endDate])
        ->groupBy('bdc_building_id', 'bdc_apartment_id', 'previous_owed')->get();
    }

    public function findMaxVersionByCurrentMonthVersion2($buildingId, $showApartment = true)
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        if($showApartment) 
        {
            return DB::select(DB::raw('SELECT `bdc_building_id`, `bdc_apartment_id`, SUM(previous_owed) AS total_owed, SUM(IF(is_free=1, sumery, 0)) as sumery_free, SUM(sumery) as sumery 
                FROM (
                    SELECT bdc_building_id, bdc_apartment_id, MAX(`version`), previous_owed, sumery, is_free FROM `bdc_debit_detail` 
                    WHERE (`bdc_building_id` = :buildingId) AND `bdc_debit_detail`.`deleted_at` IS NULL 
                        AND `bdc_debit_detail`.`to_date` >= :startDate AND `bdc_debit_detail`.`to_date` <= :endDate
                    GROUP BY `bdc_building_id`, `bdc_apartment_id`, previous_owed, sumery
                ) AS tb1 GROUP BY `bdc_building_id`, `bdc_apartment_id`'), ['buildingId' => $buildingId, 'startDate' => $startDate, 'endDate' => $endDate]);
        }else{
            return DB::select(DB::raw('SELECT `bdc_building_id`, SUM(previous_owed) AS total_owed, SUM(IF(is_free=1, sumery, 0)) as sumery_free, SUM(sumery) as sumery
                FROM (
                    SELECT bdc_building_id, bdc_apartment_id, MAX(`version`), previous_owed, sumery, is_free FROM `bdc_debit_detail` 
                    WHERE (`bdc_building_id` = :buildingId) AND `bdc_debit_detail`.`deleted_at` IS NULL 
                        AND `bdc_debit_detail`.`to_date` >= :startDate AND `bdc_debit_detail`.`to_date` <= :endDate
                    GROUP BY `bdc_building_id`, `bdc_apartment_id`, previous_owed, sumery
                ) AS tb1 GROUP BY `bdc_building_id`'), ['buildingId' => $buildingId, 'startDate' => $startDate, 'endDate' => $endDate]);
        }        
    }

    public function findMaxVersionByCurrentMonthVersion2_NoFree($buildingId, $showApartment = true)
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        if($showApartment) 
        {
            return DB::select(DB::raw('SELECT `bdc_building_id`, `bdc_apartment_id`, SUM(previous_owed) AS total_owed, SUM(sumery) AS sumery FROM (
                SELECT bdc_building_id, bdc_apartment_id, MAX(`version`), previous_owed, sumery FROM `bdc_debit_detail` 
                WHERE (`bdc_building_id` = :buildingId) AND `bdc_debit_detail`.`deleted_at` IS NULL 
                    AND `bdc_debit_detail`.`to_date` >= :startDate AND `bdc_debit_detail`.`to_date` <= :endDate
                    AND `bdc_debit_detail`.`is_free` = 0
                GROUP BY `bdc_building_id`, `bdc_apartment_id`, previous_owed, sumery
                ) AS tb1 GROUP BY `bdc_building_id`, `bdc_apartment_id`'), ['buildingId' => $buildingId, 'startDate' => $startDate, 'endDate' => $endDate]);
        }else{
            return DB::select(DB::raw('SELECT `bdc_building_id`, SUM(previous_owed) AS total_owed, SUM(sumery) AS sumery FROM (
                SELECT bdc_building_id, bdc_apartment_id, MAX(`version`), previous_owed, sumery FROM `bdc_debit_detail` 
                WHERE (`bdc_building_id` = :buildingId) AND `bdc_debit_detail`.`deleted_at` IS NULL 
                    AND `bdc_debit_detail`.`to_date` >= :startDate AND `bdc_debit_detail`.`to_date` <= :endDate
                    AND `bdc_debit_detail`.`is_free` = 0
                GROUP BY `bdc_building_id`, `bdc_apartment_id`, previous_owed, sumery
                ) AS tb1 GROUP BY `bdc_building_id`'), ['buildingId' => $buildingId, 'startDate' => $startDate, 'endDate' => $endDate]);
        }        
    }

    public function findMaxVersionByBillId($billId)
    {
        return DB::select(DB::raw('SELECT * FROM `bdc_debit_detail`
            INNER JOIN (
                SELECT bdc_bill_id, bdc_apartment_service_price_id, MAX(version) as version
                FROM `bdc_debit_detail` where `bdc_bill_id` = :billId AND `bdc_debit_detail`.`deleted_at` IS NULL
                GROUP BY bdc_bill_id, bdc_apartment_service_price_id) as tb1
            ON tb1.`bdc_bill_id`=`bdc_debit_detail`.`bdc_bill_id` 
            AND tb1.`bdc_apartment_service_price_id`=`bdc_debit_detail`.`bdc_apartment_service_price_id`
            AND tb1.`version`=`bdc_debit_detail`.`version` AND `bdc_debit_detail`.`deleted_at` IS NULL'), ['billId' => $billId]);
    }
    public static function findMaxVersionSumeryByBillId($billId)
    {
        return DB::select(DB::raw('SELECT * FROM `bdc_debit_detail`
            INNER JOIN (
                SELECT bdc_bill_id, bdc_apartment_service_price_id, MAX(version) as version
                FROM `bdc_debit_detail` where `bdc_bill_id` = :billId AND `bdc_debit_detail`.`deleted_at` IS NULL
                GROUP BY bdc_bill_id, bdc_apartment_service_price_id) as tb1
            ON tb1.`bdc_bill_id`=`bdc_debit_detail`.`bdc_bill_id` 
            AND tb1.`bdc_apartment_service_price_id`=`bdc_debit_detail`.`bdc_apartment_service_price_id`
            AND tb1.`version`=`bdc_debit_detail`.`version` AND `bdc_debit_detail`.`deleted_at` IS NULL  '), ['billId' => $billId]);
    }

    public function findMaxVersionWithNewSumeryDiffZero_v2($buildingId, $apartmentId, $serviceId, $toDate, $fromDate)
    {
        $fromDate = isset($fromDate) ? Carbon::parse($fromDate)->format('Y-m-d') : null;
        $toDate   = isset($toDate) ? Carbon::parse($toDate)->format('Y-m-d') : null;
        if($serviceId > 0) {
            return DB::select(DB::raw("SELECT `bdc_debit_detail`.*, `bdc_bills`.`status` as `bill_status` FROM `bdc_debit_detail`
            INNER JOIN (
                SELECT bdc_bill_id, bdc_apartment_service_price_id, MAX(version) as version
                FROM `bdc_debit_detail`
                WHERE `bdc_debit_detail`.`bdc_building_id` = :buildingId
                    AND `bdc_debit_detail`.`bdc_apartment_id` = :apartmentId AND `bdc_debit_detail`.`bdc_service_id` = :serviceId AND `bdc_debit_detail`.`deleted_at` is null
                GROUP BY bdc_bill_id, bdc_apartment_service_price_id) as tb1
            ON tb1.`bdc_bill_id`=`bdc_debit_detail`.`bdc_bill_id` 
            INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id`
            WHERE tb1.`bdc_apartment_service_price_id`=`bdc_debit_detail`.`bdc_apartment_service_price_id` AND `bdc_debit_detail`.`deleted_at` is null
            AND tb1.`version`=`bdc_debit_detail`.`version`
            AND `bdc_debit_detail`.`new_sumery` <> 0 
            AND `bdc_bills`.`status` > 0 
            ORDER BY `bdc_debit_detail`.`created_at` DESC"), 
            ['buildingId' => $buildingId, 'apartmentId' => $apartmentId, 'serviceId' => $serviceId]);    
        }
        else if ($toDate != null && $fromDate!= null) {
            if($serviceId > 0) {
                return DB::select(DB::raw("SELECT `bdc_debit_detail`.*, `bdc_bills`.`status` as `bill_status` FROM `bdc_debit_detail`
                INNER JOIN (
                    SELECT bdc_bill_id, bdc_apartment_service_price_id, MAX(version) as version
                    FROM `bdc_debit_detail`
                    WHERE `bdc_debit_detail`.`bdc_building_id` = :buildingId
                        AND `bdc_debit_detail`.`bdc_apartment_id` = :apartmentId AND `bdc_debit_detail`.`bdc_service_id` = :serviceId
                        AND `bdc_debit_detail`.`to_date` >= :toDate AND `bdc_debit_detail`.`from_date` <= :fromDate AND `bdc_debit_detail`.`deleted_at` is null
                    GROUP BY bdc_bill_id, bdc_apartment_service_price_id) as tb1
                ON tb1.`bdc_bill_id`=`bdc_debit_detail`.`bdc_bill_id` 
                INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id`
                WHERE tb1.`bdc_apartment_service_price_id`=`bdc_debit_detail`.`bdc_apartment_service_price_id` AND `bdc_debit_detail`.`deleted_at` is null
                AND tb1.`version`=`bdc_debit_detail`.`version` 
                AND `bdc_debit_detail`.`new_sumery` <> 0 
                AND `bdc_bills`.`status` > 0 
                ORDER BY `bdc_debit_detail`.`created_at` DESC"), 
                ['buildingId' => $buildingId, 'apartmentId' => $apartmentId, 'serviceId' => $serviceId, 'toDate' => $toDate, 'fromDate' => $fromDate]);  
            }else{
                return DB::select(DB::raw("SELECT `bdc_debit_detail`.*, `bdc_bills`.`status` as `bill_status` FROM `bdc_debit_detail`
                INNER JOIN (
                    SELECT bdc_bill_id, bdc_apartment_service_price_id, MAX(version) as version
                    FROM `bdc_debit_detail`
                    WHERE `bdc_debit_detail`.`bdc_building_id` = :buildingId
                        AND `bdc_debit_detail`.`bdc_apartment_id` = :apartmentId
                        AND `bdc_debit_detail`.`to_date` >= :toDate AND `bdc_debit_detail`.`from_date` <= :fromDate AND `bdc_debit_detail`.`deleted_at` is null
                    GROUP BY bdc_bill_id, bdc_apartment_service_price_id) as tb1
                ON tb1.`bdc_bill_id`=`bdc_debit_detail`.`bdc_bill_id` 
                INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id`
                WHERE tb1.`bdc_apartment_service_price_id`=`bdc_debit_detail`.`bdc_apartment_service_price_id` AND `bdc_debit_detail`.`deleted_at` is null
                AND tb1.`version`=`bdc_debit_detail`.`version` 
                AND `bdc_debit_detail`.`new_sumery` <> 0 
                AND `bdc_bills`.`status` > 0 
                ORDER BY `bdc_debit_detail`.`created_at` DESC"), 
                ['buildingId' => $buildingId, 'apartmentId' => $apartmentId, 'toDate' => $toDate, 'fromDate' => $fromDate]);  
            }
        }
        return DB::select(DB::raw("SELECT `bdc_debit_detail`.*, `bdc_bills`.`status` as `bill_status` FROM `bdc_debit_detail`
        INNER JOIN (
            SELECT bdc_bill_id, bdc_apartment_service_price_id, MAX(version) as version
            FROM `bdc_debit_detail`
            WHERE `bdc_debit_detail`.`bdc_building_id` = :buildingId
                AND `bdc_debit_detail`.`bdc_apartment_id` = :apartmentId AND `bdc_debit_detail`.`deleted_at` is null
            GROUP BY bdc_bill_id, bdc_apartment_service_price_id) as tb1
        ON tb1.`bdc_bill_id`=`bdc_debit_detail`.`bdc_bill_id` 
        INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id`
        INNER JOIN `bdc_services` ON `bdc_debit_detail`.`bdc_service_id` = `bdc_services`.`id`
        WHERE tb1.`bdc_apartment_service_price_id`=`bdc_debit_detail`.`bdc_apartment_service_price_id` AND `bdc_debit_detail`.`deleted_at` is null
        AND `bdc_services`.`deleted_at` is null
        AND tb1.`version`=`bdc_debit_detail`.`version` 
        AND `bdc_debit_detail`.`new_sumery`  <> 0 
        AND `bdc_bills`.`status` >= -2
        ORDER BY `bdc_debit_detail`.`created_at` DESC"), 
        ['buildingId' => $buildingId, 'apartmentId' => $apartmentId]);
    }
    public function findMaxVersionWithNewSumeryDiffZero_v3($buildingId, $apartmentId, $serviceId, $toDate, $fromDate)
    {
        return DB::select(DB::raw("SELECT `bdc_debit_detail`.*, `bdc_bills`.`status` as `bill_status` FROM `bdc_debit_detail`
        INNER JOIN (
            SELECT bdc_bill_id, bdc_apartment_service_price_id, MAX(version) as version
            FROM `bdc_debit_detail`
            WHERE `bdc_debit_detail`.`bdc_building_id` = :buildingId
                AND `bdc_debit_detail`.`bdc_apartment_id` = :apartmentId AND `bdc_debit_detail`.`deleted_at` is null
            GROUP BY bdc_bill_id, bdc_apartment_service_price_id) as tb1
        ON tb1.`bdc_bill_id`=`bdc_debit_detail`.`bdc_bill_id` 
        INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id`
        INNER JOIN `bdc_services` ON `bdc_debit_detail`.`bdc_service_id` = `bdc_services`.`id`
        WHERE tb1.`bdc_apartment_service_price_id`=`bdc_debit_detail`.`bdc_apartment_service_price_id` AND `bdc_debit_detail`.`deleted_at` is null
        AND `bdc_services`.`deleted_at` is null
        AND tb1.`version`=`bdc_debit_detail`.`version` 
        AND `bdc_debit_detail`.`new_sumery`  <> 0 
        AND `bdc_debit_detail`.`new_sumery` - `bdc_debit_detail`.`paid_v3` > 0
        AND `bdc_bills`.`status` >= -2
        ORDER BY `bdc_debit_detail`.`created_at` DESC"), 
        ['buildingId' => $buildingId, 'apartmentId' => $apartmentId]);
    }

    public function findMaxVersionWithNewSumeryDiffZero($buildingId, $apartmentId, $serviceId, $toDate, $fromDate)
    {
        $fromDate = isset($fromDate) ? Carbon::parse($fromDate)->format('Y-m-d') : null;
        $toDate   = isset($toDate) ? Carbon::parse($toDate)->format('Y-m-d') : null;
        if($serviceId > 0) {
            return DB::select(DB::raw("SELECT `bdc_debit_detail`.*, `bdc_bills`.`status` as `bill_status` FROM `bdc_debit_detail`
            INNER JOIN (
                SELECT bdc_bill_id, bdc_apartment_service_price_id, MAX(version) as version
                FROM `bdc_debit_detail`
                WHERE `bdc_debit_detail`.`bdc_building_id` = :buildingId
                    AND `bdc_debit_detail`.`bdc_apartment_id` = :apartmentId AND `bdc_debit_detail`.`bdc_service_id` = :serviceId AND `bdc_debit_detail`.`deleted_at` is null
                GROUP BY bdc_bill_id, bdc_apartment_service_price_id) as tb1
            ON tb1.`bdc_bill_id`=`bdc_debit_detail`.`bdc_bill_id` 
            INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id`
            WHERE tb1.`bdc_apartment_service_price_id`=`bdc_debit_detail`.`bdc_apartment_service_price_id` AND `bdc_debit_detail`.`deleted_at` is null
            AND tb1.`version`=`bdc_debit_detail`.`version`
            AND `bdc_debit_detail`.`new_sumery` <> 0 
            AND `bdc_bills`.`status` > 0 
            ORDER BY `bdc_debit_detail`.`created_at` DESC"), 
            ['buildingId' => $buildingId, 'apartmentId' => $apartmentId, 'serviceId' => $serviceId]);    
        }
        else if ($toDate != null && $fromDate!= null) {
            if($serviceId > 0) {
                return DB::select(DB::raw("SELECT `bdc_debit_detail`.*, `bdc_bills`.`status` as `bill_status` FROM `bdc_debit_detail`
                INNER JOIN (
                    SELECT bdc_bill_id, bdc_apartment_service_price_id, MAX(version) as version
                    FROM `bdc_debit_detail`
                    WHERE `bdc_debit_detail`.`bdc_building_id` = :buildingId
                        AND `bdc_debit_detail`.`bdc_apartment_id` = :apartmentId AND `bdc_debit_detail`.`bdc_service_id` = :serviceId
                        AND `bdc_debit_detail`.`to_date` >= :toDate AND `bdc_debit_detail`.`from_date` <= :fromDate AND `bdc_debit_detail`.`deleted_at` is null
                    GROUP BY bdc_bill_id, bdc_apartment_service_price_id) as tb1
                ON tb1.`bdc_bill_id`=`bdc_debit_detail`.`bdc_bill_id` 
                INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id`
                WHERE tb1.`bdc_apartment_service_price_id`=`bdc_debit_detail`.`bdc_apartment_service_price_id` AND `bdc_debit_detail`.`deleted_at` is null
                AND tb1.`version`=`bdc_debit_detail`.`version` 
                AND `bdc_debit_detail`.`new_sumery` <> 0 
                AND `bdc_bills`.`status` > 0 
                ORDER BY `bdc_debit_detail`.`created_at` DESC"), 
                ['buildingId' => $buildingId, 'apartmentId' => $apartmentId, 'serviceId' => $serviceId, 'toDate' => $toDate, 'fromDate' => $fromDate]);  
            }else{
                return DB::select(DB::raw("SELECT `bdc_debit_detail`.*, `bdc_bills`.`status` as `bill_status` FROM `bdc_debit_detail`
                INNER JOIN (
                    SELECT bdc_bill_id, bdc_apartment_service_price_id, MAX(version) as version
                    FROM `bdc_debit_detail`
                    WHERE `bdc_debit_detail`.`bdc_building_id` = :buildingId
                        AND `bdc_debit_detail`.`bdc_apartment_id` = :apartmentId
                        AND `bdc_debit_detail`.`to_date` >= :toDate AND `bdc_debit_detail`.`from_date` <= :fromDate AND `bdc_debit_detail`.`deleted_at` is null
                    GROUP BY bdc_bill_id, bdc_apartment_service_price_id) as tb1
                ON tb1.`bdc_bill_id`=`bdc_debit_detail`.`bdc_bill_id` 
                INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id`
                WHERE tb1.`bdc_apartment_service_price_id`=`bdc_debit_detail`.`bdc_apartment_service_price_id` AND `bdc_debit_detail`.`deleted_at` is null
                AND tb1.`version`=`bdc_debit_detail`.`version` 
                AND `bdc_debit_detail`.`new_sumery` <> 0 
                AND `bdc_bills`.`status` > 0 
                ORDER BY `bdc_debit_detail`.`created_at` DESC"), 
                ['buildingId' => $buildingId, 'apartmentId' => $apartmentId, 'toDate' => $toDate, 'fromDate' => $fromDate]);  
            }
        }
        return DB::select(DB::raw("SELECT `bdc_debit_detail`.*, `bdc_bills`.`status` as `bill_status` FROM `bdc_debit_detail`
        INNER JOIN (
            SELECT bdc_bill_id, bdc_apartment_service_price_id, MAX(version) as version
            FROM `bdc_debit_detail`
            WHERE `bdc_debit_detail`.`bdc_building_id` = :buildingId
                AND `bdc_debit_detail`.`bdc_apartment_id` = :apartmentId AND `bdc_debit_detail`.`deleted_at` is null
            GROUP BY bdc_bill_id, bdc_apartment_service_price_id) as tb1
        ON tb1.`bdc_bill_id`=`bdc_debit_detail`.`bdc_bill_id` 
        INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id`
        WHERE tb1.`bdc_apartment_service_price_id`=`bdc_debit_detail`.`bdc_apartment_service_price_id` AND `bdc_debit_detail`.`deleted_at` is null
        AND tb1.`version`=`bdc_debit_detail`.`version` 
        AND `bdc_debit_detail`.`new_sumery` <> 0 
        AND `bdc_bills`.`status` > 0 
        ORDER BY `bdc_debit_detail`.`created_at` DESC"), 
        ['buildingId' => $buildingId, 'apartmentId' => $apartmentId]);
    }
    
    public function findMaxVersionWithPhieuChi($buildingId, $apartmentId, $serviceId, $toDate, $fromDate)
    {
        $fromDate = isset($fromDate) ? Carbon::parse($fromDate)->format('Y-m-d') : null;
        $toDate   = isset($toDate) ? Carbon::parse($toDate)->format('Y-m-d') : null;
        if($serviceId > 0) {
            return DB::select(DB::raw("SELECT `bdc_debit_detail`.*, `bdc_bills`.`status` as `bill_status` FROM `bdc_debit_detail`
            INNER JOIN (
                SELECT bdc_bill_id, bdc_apartment_service_price_id, MAX(version) as version
                FROM `bdc_debit_detail`
                WHERE `bdc_debit_detail`.`bdc_building_id` = :buildingId
                    AND `bdc_debit_detail`.`bdc_apartment_id` = :apartmentId AND `bdc_debit_detail`.`bdc_service_id` = :serviceId AND `bdc_debit_detail`.`deleted_at` is null
                GROUP BY bdc_bill_id, bdc_apartment_service_price_id) as tb1
            ON tb1.`bdc_bill_id`=`bdc_debit_detail`.`bdc_bill_id` 
            INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id`
            WHERE tb1.`bdc_apartment_service_price_id`=`bdc_debit_detail`.`bdc_apartment_service_price_id` AND `bdc_debit_detail`.`deleted_at` is null
            AND tb1.`version`=`bdc_debit_detail`.`version`
            AND `bdc_bills`.`status` > 0 
            ORDER BY `bdc_debit_detail`.`created_at` DESC"), 
            ['buildingId' => $buildingId, 'apartmentId' => $apartmentId, 'serviceId' => $serviceId]);    
        }
        else if ($toDate != null && $fromDate!= null) {
            if($serviceId > 0) {
                return DB::select(DB::raw("SELECT `bdc_debit_detail`.*, `bdc_bills`.`status` as `bill_status` FROM `bdc_debit_detail`
                INNER JOIN (
                    SELECT bdc_bill_id, bdc_apartment_service_price_id, MAX(version) as version
                    FROM `bdc_debit_detail`
                    WHERE `bdc_debit_detail`.`bdc_building_id` = :buildingId
                        AND `bdc_debit_detail`.`bdc_apartment_id` = :apartmentId AND `bdc_debit_detail`.`bdc_service_id` = :serviceId
                        AND `bdc_debit_detail`.`to_date` >= :toDate AND `bdc_debit_detail`.`from_date` <= :fromDate AND `bdc_debit_detail`.`deleted_at` is null
                    GROUP BY bdc_bill_id, bdc_apartment_service_price_id) as tb1
                ON tb1.`bdc_bill_id`=`bdc_debit_detail`.`bdc_bill_id` 
                INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id`
                WHERE tb1.`bdc_apartment_service_price_id`=`bdc_debit_detail`.`bdc_apartment_service_price_id` AND `bdc_debit_detail`.`deleted_at` is null
                AND tb1.`version`=`bdc_debit_detail`.`version` 
                AND `bdc_bills`.`status` > 0 
                ORDER BY `bdc_debit_detail`.`created_at` DESC"), 
                ['buildingId' => $buildingId, 'apartmentId' => $apartmentId, 'serviceId' => $serviceId, 'toDate' => $toDate, 'fromDate' => $fromDate]);  
            }else{
                return DB::select(DB::raw("SELECT `bdc_debit_detail`.*, `bdc_bills`.`status` as `bill_status` FROM `bdc_debit_detail`
                INNER JOIN (
                    SELECT bdc_bill_id, bdc_apartment_service_price_id, MAX(version) as version
                    FROM `bdc_debit_detail`
                    WHERE `bdc_debit_detail`.`bdc_building_id` = :buildingId
                        AND `bdc_debit_detail`.`bdc_apartment_id` = :apartmentId
                        AND `bdc_debit_detail`.`to_date` >= :toDate AND `bdc_debit_detail`.`from_date` <= :fromDate AND `bdc_debit_detail`.`deleted_at` is null
                    GROUP BY bdc_bill_id, bdc_apartment_service_price_id) as tb1
                ON tb1.`bdc_bill_id`=`bdc_debit_detail`.`bdc_bill_id` 
                INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id`
                WHERE tb1.`bdc_apartment_service_price_id`=`bdc_debit_detail`.`bdc_apartment_service_price_id` AND `bdc_debit_detail`.`deleted_at` is null
                AND tb1.`version`=`bdc_debit_detail`.`version` 
                AND `bdc_bills`.`status` > 0 
                ORDER BY `bdc_debit_detail`.`created_at` DESC"), 
                ['buildingId' => $buildingId, 'apartmentId' => $apartmentId, 'toDate' => $toDate, 'fromDate' => $fromDate]);  
            }
        }
        return DB::select(DB::raw("SELECT `bdc_debit_detail`.*, `bdc_bills`.`status` as `bill_status` FROM `bdc_debit_detail`
        INNER JOIN (
            SELECT bdc_bill_id, bdc_apartment_service_price_id, MAX(version) as version
            FROM `bdc_debit_detail`
            WHERE `bdc_debit_detail`.`bdc_building_id` = :buildingId
                AND `bdc_debit_detail`.`bdc_apartment_id` = :apartmentId AND `bdc_debit_detail`.`deleted_at` is null
            GROUP BY bdc_bill_id, bdc_apartment_service_price_id) as tb1
        ON tb1.`bdc_bill_id`=`bdc_debit_detail`.`bdc_bill_id` 
        INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id`
        WHERE tb1.`bdc_apartment_service_price_id`=`bdc_debit_detail`.`bdc_apartment_service_price_id` AND `bdc_debit_detail`.`deleted_at` is null
        AND tb1.`version`=`bdc_debit_detail`.`version` 
        AND `bdc_bills`.`status` > 0 
        ORDER BY `bdc_debit_detail`.`created_at` DESC"), 
        ['buildingId' => $buildingId, 'apartmentId' => $apartmentId]);
    }

    public function findMaxVersionWithNewSumeryEqualZero($buildingId, $apartmentId)
    {
        return DB::select(DB::raw("SELECT * FROM `bdc_debit_detail`
        INNER JOIN (
            SELECT bdc_bill_id, bdc_apartment_service_price_id, MAX(version) as version
            FROM `bdc_debit_detail`
            WHERE `bdc_debit_detail`.`new_sumery` = 0 AND `bdc_debit_detail`.`bdc_building_id` = :buildingId
                AND `bdc_debit_detail`.`bdc_apartment_id` = :apartmentId AND `bdc_debit_detail`.`deleted_at` is null
            GROUP BY bdc_bill_id, bdc_apartment_service_price_id) as tb1
        ON tb1.`bdc_bill_id`=`bdc_debit_detail`.`bdc_bill_id` 
        AND tb1.`bdc_apartment_service_price_id`=`bdc_debit_detail`.`bdc_apartment_service_price_id`
        AND tb1.`version`=`bdc_debit_detail`.`version` ORDER BY `bdc_debit_detail`.`created_at` DESC"), 
        ['buildingId' => $buildingId, 'apartmentId' => $apartmentId]);
    }

    public function findMaxVersionWithBuildingApartment($buildingId, $apartmentId, $billId)
    {
        return DB::select(DB::raw("SELECT SUM(new_sumery - paid_v3) as `total_payment` FROM `bdc_debit_detail`
        INNER JOIN (
            SELECT bdc_bill_id, bdc_apartment_service_price_id, MAX(version) as version
            FROM `bdc_debit_detail`
            WHERE `bdc_debit_detail`.`bdc_building_id` = :buildingId
                AND `bdc_debit_detail`.`bdc_apartment_id` = :apartmentId AND `bdc_debit_detail`.`deleted_at` is null
            GROUP BY bdc_bill_id, bdc_apartment_service_price_id) as tb1
        ON tb1.`bdc_bill_id`=`bdc_debit_detail`.`bdc_bill_id` 
        INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id` AND `bdc_bills`.`status` >= -2
        AND tb1.`bdc_apartment_service_price_id`=`bdc_debit_detail`.`bdc_apartment_service_price_id`
        AND tb1.`version`=`bdc_debit_detail`.`version` 
        WHERE `bdc_debit_detail`.`bdc_bill_id` <> :billId AND `bdc_debit_detail`.`deleted_at` is null 
        ORDER BY `bdc_debit_detail`.`created_at` DESC"), 
        ['buildingId' => $buildingId, 'apartmentId' => $apartmentId, 'billId' => $billId]);
    }

    public function findMaxVersionWithBuildingApartment_old($buildingId, $apartmentId, $billId)
    {
        return DB::select(DB::raw("SELECT SUM(new_sumery) as `total_payment` FROM `bdc_debit_detail`
        INNER JOIN (
            SELECT bdc_bill_id, bdc_apartment_service_price_id, MAX(version) as version
            FROM `bdc_debit_detail`
            WHERE `bdc_debit_detail`.`bdc_building_id` = :buildingId
                AND `bdc_debit_detail`.`bdc_apartment_id` = :apartmentId AND `bdc_debit_detail`.`deleted_at` is null
            GROUP BY bdc_bill_id, bdc_apartment_service_price_id) as tb1
        ON tb1.`bdc_bill_id`=`bdc_debit_detail`.`bdc_bill_id` 
        INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id` AND `bdc_bills`.`status` >= -2
        AND tb1.`bdc_apartment_service_price_id`=`bdc_debit_detail`.`bdc_apartment_service_price_id`
        AND tb1.`version`=`bdc_debit_detail`.`version` 
        WHERE `bdc_debit_detail`.`bdc_bill_id` <> :billId AND `bdc_debit_detail`.`deleted_at` is null 
        ORDER BY `bdc_debit_detail`.`created_at` DESC"), 
        ['buildingId' => $buildingId, 'apartmentId' => $apartmentId, 'billId' => $billId]);
    }

    public function findMaxVersionByBillId1($billId)
    {
        return DB::select(DB::raw('SELECT *,`tb2`.`name` as `name_service` FROM `bdc_debit_detail`
            INNER JOIN (
                SELECT bdc_bill_id, bdc_apartment_service_price_id, MAX(version) as version
                FROM `bdc_debit_detail` where `bdc_bill_id` = :billId 
                GROUP BY bdc_bill_id, bdc_apartment_service_price_id) as tb1
            ON tb1.`bdc_bill_id`=`bdc_debit_detail`.`bdc_bill_id` INNER JOIN `bdc_services` as tb2 ON tb2.`id` =  `bdc_debit_detail`.`bdc_service_id`
            AND tb1.`bdc_apartment_service_price_id`=`bdc_debit_detail`.bdc_apartment_service_price_id 
            AND tb1.`version`=`bdc_debit_detail`.version AND `bdc_debit_detail`.`deleted_at` is null' ) , ['billId' => $billId]);
    }

    public static function findMaxVersionWithBillId($billId)
    {
        return DB::select(DB::raw('SELECT *,`tb2`.`name` as `name_service` FROM `bdc_debit_detail`
            INNER JOIN (
                SELECT bdc_bill_id, bdc_apartment_service_price_id, MAX(version) as version
                FROM `bdc_debit_detail` where `bdc_bill_id` = :billId 
                GROUP BY bdc_bill_id, bdc_apartment_service_price_id) as tb1
            ON tb1.`bdc_bill_id`=`bdc_debit_detail`.`bdc_bill_id` INNER JOIN `bdc_services` as tb2 ON tb2.`id` =  `bdc_debit_detail`.`bdc_service_id`
            AND tb1.`bdc_apartment_service_price_id`=`bdc_debit_detail`.bdc_apartment_service_price_id 
            AND tb1.`version`=`bdc_debit_detail`.version AND `bdc_debit_detail`.`deleted_at` is null' ) , ['billId' => $billId]);
    }

    public static function findMaxVersionWithBillIdV2($billId)
    {
        return DB::select(DB::raw('SELECT *,`tb2`.`name` as `name_service` FROM `bdc_debit_detail`
            INNER JOIN (
                SELECT bdc_bill_id, bdc_apartment_service_price_id, MAX(version) as version
                FROM `bdc_debit_detail` where `bdc_bill_id` = :billId 
                GROUP BY bdc_bill_id, bdc_apartment_service_price_id) as tb1
            ON tb1.`bdc_bill_id`=`bdc_debit_detail`.`bdc_bill_id` INNER JOIN `bdc_services` as tb2 ON tb2.`id` =  `bdc_debit_detail`.`bdc_service_id`
            INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id`
            AND `bdc_bills`.`status` > -3
            AND tb1.`bdc_apartment_service_price_id`=`bdc_debit_detail`.bdc_apartment_service_price_id 
            AND tb1.`version`=`bdc_debit_detail`.version AND `bdc_debit_detail`.`deleted_at` is null' ) , ['billId' => $billId]);
    }

    public static function findMaxVersionPaid($billId)
    {
        return DB::select(DB::raw('SELECT `bdc_building_id`, `bdc_apartment_id`, SUM(paid) AS total_paid FROM (
            SELECT bdc_building_id, bdc_apartment_id, MAX(`version`), paid FROM `bdc_debit_detail` 
                WHERE `bdc_debit_detail`.`bdc_bill_id` = :billId AND `bdc_debit_detail`.`deleted_at` IS NULL 
            GROUP BY `bdc_building_id`, `bdc_apartment_id`, paid
        ) AS tb1 GROUP BY `bdc_building_id`, `bdc_apartment_id`'), ['billId' => $billId]);
    }
    public static function findMaxVersionPaid_v2($buildingId, $apartmentId, $billId)
    {
        return DB::select(DB::raw("SELECT SUM(paid + paid_v3) AS total_paid FROM `bdc_debit_detail`
        INNER JOIN (
            SELECT bdc_bill_id, bdc_apartment_service_price_id
            FROM `bdc_debit_detail`
            WHERE `bdc_debit_detail`.`bdc_building_id` = :buildingId
                AND `bdc_debit_detail`.`bdc_apartment_id` = :apartmentId AND `bdc_debit_detail`.`deleted_at` is null
            GROUP BY bdc_bill_id, bdc_apartment_service_price_id) as tb1
        ON tb1.`bdc_bill_id`=`bdc_debit_detail`.`bdc_bill_id` 
        INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id`
        AND tb1.`bdc_apartment_service_price_id`=`bdc_debit_detail`.`bdc_apartment_service_price_id`
        WHERE `bdc_debit_detail`.`bdc_bill_id` = :billId AND `bdc_debit_detail`.`deleted_at` is null 
        ORDER BY `bdc_debit_detail`.`created_at` DESC"), 
        ['buildingId' => $buildingId, 'apartmentId' => $apartmentId, 'billId' => $billId]);
    }

    public static function findMaxVersionPaid_v2_old($buildingId, $apartmentId, $billId)
    {
        return DB::select(DB::raw("SELECT SUM(paid) AS total_paid FROM `bdc_debit_detail`
        INNER JOIN (
            SELECT bdc_bill_id, bdc_apartment_service_price_id
            FROM `bdc_debit_detail`
            WHERE `bdc_debit_detail`.`bdc_building_id` = :buildingId
                AND `bdc_debit_detail`.`bdc_apartment_id` = :apartmentId AND `bdc_debit_detail`.`deleted_at` is null
            GROUP BY bdc_bill_id, bdc_apartment_service_price_id) as tb1
        ON tb1.`bdc_bill_id`=`bdc_debit_detail`.`bdc_bill_id` 
        INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id`
        AND tb1.`bdc_apartment_service_price_id`=`bdc_debit_detail`.`bdc_apartment_service_price_id`
        WHERE `bdc_debit_detail`.`bdc_bill_id` = :billId AND `bdc_debit_detail`.`deleted_at` is null 
        ORDER BY `bdc_debit_detail`.`created_at` DESC"), 
        ['buildingId' => $buildingId, 'apartmentId' => $apartmentId, 'billId' => $billId]);
    }

    public static function findMaxVersionPaidVersion2($billId)
    {
        return DB::select(DB::raw('SELECT `bdc_building_id`, `bdc_apartment_id`, SUM(paid + paid_v3) AS total_paid 
            FROM `bdc_debit_detail` 
            WHERE `bdc_debit_detail`.`bdc_bill_id` = :billId AND `bdc_debit_detail`.`deleted_at` IS NULL
		    GROUP BY `bdc_building_id`, `bdc_apartment_id`, paid'), ['billId' => $billId]);
    }

    public static function findMaxVersionPaidVersion3($billId)
    {
        return DB::select(DB::raw('SELECT `bdc_debit_detail`.`bdc_building_id`, `bdc_debit_detail`.`bdc_apartment_id`, SUM(paid + paid_v3) AS total_paid 
            FROM `bdc_debit_detail` 
            INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id`
            WHERE `bdc_debit_detail`.`bdc_bill_id` = :billId AND `bdc_bills`.`status` > -3 AND `bdc_debit_detail`.`deleted_at` IS NULL
		    GROUP BY `bdc_debit_detail`.`bdc_building_id`, `bdc_debit_detail`.`bdc_apartment_id`, paid'), ['billId' => $billId]);
    }

    public static function findMaxVersionPaidVersion2_old($billId)
    {
        return DB::select(DB::raw('SELECT `bdc_building_id`, `bdc_apartment_id`, SUM(paid) AS total_paid 
            FROM `bdc_debit_detail` 
            WHERE `bdc_debit_detail`.`bdc_bill_id` = :billId AND `bdc_debit_detail`.`deleted_at` IS NULL
		    GROUP BY `bdc_building_id`, `bdc_apartment_id`, paid'), ['billId' => $billId]);
    }

    public static function findMaxVersionPaid_NoFree($billId)
    {
        return DB::select(DB::raw('SELECT `bdc_building_id`, `bdc_apartment_id`, SUM(paid) AS total_paid FROM (
            SELECT bdc_building_id, bdc_apartment_id, MAX(`version`), paid FROM `bdc_debit_detail` 
                WHERE `bdc_debit_detail`.`bdc_bill_id` = :billId AND `bdc_debit_detail`.`deleted_at` IS NULL
                    AND `bdc_debit_detail`.`is_free` = 0
            GROUP BY `bdc_building_id`, `bdc_apartment_id`, paid
        ) AS tb1 GROUP BY `bdc_building_id`, `bdc_apartment_id`'), ['billId' => $billId]);
    }

    public function findMaxVersionByBillApartmentServiceId($billId, $apartmentServicePriceId)
    {
        return DB::select(DB::raw('SELECT `bdc_debit_detail`.*,`bdc_services`.`name` FROM `bdc_debit_detail`
            INNER JOIN (
                SELECT bdc_bill_id, bdc_apartment_service_price_id, MAX(version) as version
                FROM `bdc_debit_detail` where `bdc_bill_id` = :billId AND bdc_apartment_service_price_id = :apartmentServicePriceId  AND `bdc_debit_detail`.`deleted_at` is null
                GROUP BY bdc_bill_id, bdc_apartment_service_price_id) as tb1
            ON tb1.`bdc_bill_id`=`bdc_debit_detail`.`bdc_bill_id` 
            INNER JOIN `bdc_services` ON `bdc_services`.`id` = `bdc_debit_detail`.`bdc_service_id`
            AND tb1.`bdc_apartment_service_price_id`=`bdc_debit_detail`.bdc_apartment_service_price_id 
            AND tb1.`version`=`bdc_debit_detail`.version AND `bdc_debit_detail`.`deleted_at` is null limit 1'), ['billId' => $billId, 'apartmentServicePriceId' => $apartmentServicePriceId]);
    }
    public function findMaxVersionByBillApartmentServiceIdV2($billId, $apartmentServicePriceId,$cycleName)
    {
        return DB::select(DB::raw('SELECT * FROM `bdc_debit_detail`
            INNER JOIN (
                SELECT bdc_bill_id, bdc_apartment_service_price_id, MAX(version) as version
                FROM `bdc_debit_detail` where `bdc_bill_id` = :billId AND bdc_apartment_service_price_id = :apartmentServicePriceId  AND `bdc_debit_detail`.`deleted_at` is null
                GROUP BY bdc_bill_id, bdc_apartment_service_price_id) as tb1
            ON tb1.`bdc_bill_id`=`bdc_debit_detail`.`bdc_bill_id` 
            AND tb1.`bdc_apartment_service_price_id`=`bdc_debit_detail`.bdc_apartment_service_price_id 
            AND tb1.`version`=`bdc_debit_detail`.version AND `bdc_debit_detail`.`deleted_at` is null AND `bdc_debit_detail`.`cycle_name` <> :cycleName'), ['billId' => $billId, 'apartmentServicePriceId' => $apartmentServicePriceId,'cycleName'=>$cycleName]);
    }

    public function getAll()
    {
        return $this->model->paginate(PAGE);
    }

    public function importFileDienNuoc(
        $request,
        $cronJobManager,
        $buildingId,
        $apartmentServicePrice,
        $customer,
        $service,
        $apartmentRepository,
        $debitLogs
    ) {
        set_time_limit(0);
        $file = $request->file('file_import');
        $path = $file->getRealPath();
     
        $excel_data = Excel::load($path)->get();
        storage_path('upload', $file->getClientOriginalName());
        
        $url = [
            'name' => $file->getClientOriginalName(),
            'uuid' => (string) \Webpatser\Uuid\Uuid::generate(),
        ];

        //  check xem trạng thái dienuocdebitprocess:cron status = 0 
        $cronJob = $cronJobManager->findSignatureBuildingId('dienuocdebitprocess:cron', $buildingId)->first();
        if (!$cronJob) {   // nếu không có thì tạo mới với status = -1
            $cronJob = $cronJobManager->create([
                'building_id' => $buildingId,
                'user_id' => auth()->user()->id,
                'signature' => 'dienuocdebitprocess:cron',
                'status' => -1,
                'deadline' =>$request['deadline'] ? Carbon::parse($request['deadline'])->format('Y-m-d') : Carbon::now()->format('Y-m-d')
            ]);
        }
        $cycleName = $request['deadline'] ? Carbon::parse($request['deadline'])->format('Ym') : Carbon::now()->format('Ym');
        if ($excel_data->count()) {
            $count = 0;
            foreach ($excel_data as $data) {
                $exceldataJson = json_encode($data);
                if($data->ma_can_ho == null)
                {
                    $debitLogs->create([
                        'bdc_building_id' => $buildingId,
                        'bdc_apartment_id' => $data->ma_can_ho,
                        'bdc_service_id' => $data->ma_dich_vu,
                        'key' => "import_dien_nuoc",
                        'cycle_name' => $cycleName,
                        'input' => $exceldataJson,
                        'data' => "",
                        'message' => "Mã căn hộ không được để trống.",
                        'status' => 100
                    ]);
                    continue;
                }

                if($data->ky_thang == null)
                {
                    $debitLogs->create([
                        'bdc_building_id' => $buildingId,
                        'bdc_apartment_id' => $data->ma_can_ho,
                        'bdc_service_id' => $data->ma_dich_vu,
                        'key' => "import_dien_nuoc",
                        'cycle_name' => $cycleName,
                        'input' => $exceldataJson,
                        'data' => "",
                        'message' => "Mã căn hộ $data->ma_can_ho không có kỳ tháng.",
                        'status' => 100
                    ]);
                    continue;
                }

                // $_apartment = $apartmentRepository->findByCode($data->ma_can_ho);
                $_apartment = Apartments::where(['code'=> trim($data->ma_can_ho),'building_id'=>$buildingId])->first();
                if($_apartment == null) {
                    $debitLogs->create([
                        'bdc_building_id' => $buildingId,
                        'bdc_apartment_id' => $data->ma_can_ho,
                        'bdc_service_id' => $data->ma_dich_vu,
                        'key' => "import_dien_nuoc",
                        'cycle_name' => $cycleName,
                        'input' => $exceldataJson,
                        'data' => "",
                        'message' => "Mã căn hộ $data->ma_can_ho không tồn tại.",
                        'status' => 100
                    ]);
                    continue;
                }
               
                // $_apartmentServicePrice = $apartmentServicePrice->findApartmentUsingDienNuoc($buildingId, $_apartment->id, $data->ma_dich_vu);
                $_apartmentServicePrice = ApartmentServicePrice::where('bdc_building_id', $buildingId)
                    ->where('bdc_apartment_id', $_apartment->id)
                    ->where('bdc_service_id', $data->ma_dich_vu)
                    ->where('bdc_price_type_id', $apartmentServicePrice->getMultiPrice())->first();

                if($_apartmentServicePrice == null){
                    $debitLogs->create([
                        'bdc_building_id' => $buildingId,
                        'bdc_apartment_id' => $_apartment->id,
                        'bdc_service_id' => $data->ma_dich_vu,
                        'key' => "import_dien_nuoc",
                        'cycle_name' => $cycleName,
                        'input' => $exceldataJson,
                        'data' => "",
                        'message' => "Căn hộ $data->ma_can_ho mã dịch vụ $data->ma_dich_vu không phải loại dịch vụ điện nước",
                        'status' => 101
                    ]);
                    continue;
                }
               
                $progressive = @$_apartmentServicePrice->progressive;
                $progressivePrices = @$progressive->progressivePrice;
                if(!$progressivePrices){
                    $debitLogs->create([
                        'bdc_building_id' => $buildingId,
                        'bdc_apartment_id' => $_apartment->id,
                        'bdc_service_id' => $data->ma_dich_vu,
                        'key' => "import_dien_nuoc",
                        'cycle_name' => $cycleName,
                        'input' => $exceldataJson,
                        'data' => "",
                        'message' => "Căn hộ $data->ma_can_ho dịch vụ $data->ma_dich_vu chưa có bảng giá lũy tiến",
                        'status' => 101
                    ]);
                    continue;
                }
                $price = 0;
                $totalPrice = 0;
                $dataArray = array();
                $_dataArray = array();
                foreach ($progressivePrices as $progressivePrice) {
                    // tính tổng tiền cho dich vụ điện nước
                    $soDau = $data->so_dau;
                    $soCuoi = $data->so_cuoi;
                    $totalNumber = $soCuoi - $soDau;
                    if ($progressivePrice->to >= $totalNumber) {
                        $price = ($totalNumber - $progressivePrice->from + 1) * $progressivePrice->price;
                        $_dataArray["from"] = $progressivePrice->from;
                        $_dataArray["to"] = $totalNumber;                                                          
                        $_dataArray["price"] = $progressivePrice->price;
                        $_dataArray["total_price"] = $price;
                        $totalPrice += $price;
                        array_push($dataArray, $_dataArray);
                        break;
                    } else {
                        $price = ($progressivePrice->to - $progressivePrice->from + 1) * $progressivePrice->price;
                        $_dataArray["from"] = $progressivePrice->from;
                        $_dataArray["to"] = $progressivePrice->to;                   
                        $_dataArray["price"] = $progressivePrice->price;
                        $_dataArray["total_price"] = $price;
                        $totalPrice += $price;
                        array_push($dataArray, $_dataArray);
                    }
                }
                $dataJson = json_encode($dataArray);
                $dataString = '{"so_dau": ' . $soDau . ', "so_cuoi": ' . $soCuoi . ', "tieu_thu": ' . $totalNumber . ', "data":' . $dataJson . "}";
                // lấy chủ hộ của căn hộ
                // $_customer = UserApartments::getPurchaser($_apartment->id, 0);
                $_customer = UserApartments::getPurchaser($apartmentServicePrice->bdc_apartment_id, 0);
                if($_customer == null){
                    $debitLogs->create([
                        'bdc_building_id' => $buildingId,
                        'bdc_apartment_id' => $_apartment->id,
                        'bdc_service_id' => $data->ma_dich_vu,
                        'key' => "import_dien_nuoc",
                        'cycle_name' => $cycleName,
                        'input' => $exceldataJson,
                        'data' => "",
                        'message' => "Căn hộ $data->ma_can_ho chưa có chủ hộ.",
                        'status' => 102
                    ]);
                    continue;
                }
                // Lấy ra chi tiết dịch vụ
                // $_service = $service->findService($data->ma_dich_vu);
                $_service = Service::where('status', 1)->where('id', trim($data->ma_dich_vu))->first();
                if($_service == null){
                    $debitLogs->create([
                        'bdc_building_id' => $buildingId,
                        'bdc_apartment_id' => $_apartment->id,
                        'bdc_service_id' => $data->ma_dich_vu,
                        'key' => "import_dien_nuoc",
                        'cycle_name' => $cycleName,
                        'input' => $exceldataJson,
                        'data' => "",
                        'message' => "Dịch vụ $data->ma_dich_vu chưa được đưa vào hoạt động.",
                        'status' => 103
                    ]);
                    continue;
                }
                // Lấy thông tin căn hộ
                $apartment = $_apartmentServicePrice->apartment;
                // Lấy ra from_date
                $current = Carbon::now();
                $_fromDate = $request['from_date'] ? Carbon::parse($request['from_date'])->format('Y-m-d') : Carbon::now()->format('Y-m-d');
                $toDate = $request['to_date'] ? Carbon::parse($request['to_date'])->format('Y-m-d') : Carbon::now()->format('Y-m-d');
                $customerInfo = @$_customer->user_info_first;
                if($customerInfo == null)
                {
                    $debitLogs->create([
                        'bdc_building_id' => $buildingId,
                        'bdc_apartment_id' => $_apartment->id,
                        'bdc_service_id' => $data->ma_dich_vu,
                        'key' => "import_dien_nuoc",
                        'cycle_name' => $cycleName,
                        'input' => $exceldataJson,
                        'data' => "",
                        'message' => "Thông tin người dùng không tồn tại.",
                        'status' => 104
                    ]);
                    continue;
                }
                if(ctype_digit($data->ky_thang) == false && strlen((string)$data->ky_thang) !=6)
                {
                    $debitLogs->create([
                        'bdc_building_id' => $buildingId,
                        'bdc_apartment_id' => $_apartment->id,
                        'bdc_service_id' => $data->ma_dich_vu,
                        'key' => "import_dien_nuoc",
                        'cycle_name' => $cycleName,
                        'input' => $exceldataJson,
                        'data' => "",
                        'message' => "Định dạng kỳ tháng phải là: yyyymm.",
                        'status' => 105
                    ]);
                    continue;
                }
                $_apartmentServicePrice->customer_name = $customerInfo->full_name;
                $_apartmentServicePrice->customer_address = $customerInfo->address;
                $_apartmentServicePrice->provider_address = 'test';
                $_apartmentServicePrice->deadline = $request['deadline'] ? Carbon::parse($request['deadline'])->format('Y-m-d') : Carbon::now()->format('Y-m-d');
                // $_apartmentServicePrice->from_date = $_fromDate->toDateString();
                // $_apartmentServicePrice->to_date = $toDate->toDateString();
                $_apartmentServicePrice->from_date = $_fromDate;
                $_apartmentServicePrice->to_date = $toDate;
                $_apartmentServicePrice->price = $totalPrice;
                $_apartmentServicePrice->free = 0;
                $_apartmentServicePrice->service_name = $_service->name;
                $_apartmentServicePrice->apartment_name = $apartment->name;
                $_apartmentServicePrice->detail = $dataString;
                // $_apartmentServicePrice->quantity = $quantity;
                // $_apartmentServicePrice->one_price = $onePrice;
                $_apartmentServicePrice->bdc_price_type_id = 2;
                $_apartmentServicePrice->use_bill = $request['use_bill'];
                
                // $cycle_name = Carbon::parse($fromDate)->format('m/Y');
                // $cycle_name = str_replace('_', '/', $data->ky_thang);
                $cycle_name = $data->ky_thang;
                $_apartmentServicePrice->cycle_name = $cycle_name;
                $_apartmentServicePrice->url_image =  $data->duong_dan_anh;
                
                $resultDataJson = json_encode($_apartmentServicePrice);             
              
                $debitLogs->create([
                    'bdc_building_id' => $buildingId,
                    'bdc_apartment_id' => $_apartment->id,
                    'bdc_service_id' => $data->ma_dich_vu,
                    'key' => "import_dien_nuoc",
                    'cycle_name' => $cycleName,
                    'input' => $exceldataJson,
                    'data' => $resultDataJson,
                    'message' => "Lấy thông tin thành công",
                    'status' => 100
                ]);

                QueueRedis::setItemForQueue('add_queue_apartment_service_dien_nuoc_price_' . $buildingId, $_apartmentServicePrice);
                $count++;
            }
            
            if($count > 0)
            {                
               $cronJobManager->update(['status' => 0], $cronJob->id);
            }
            else
            {
               $cronJobManager->delete(['id' => $cronJob->id]);
            }    
                 
            return true;
        }
    }

    // import phí dịch vụ
     public function importFileDauKy(
        $request,
        $cronJobManager,
        $buildingId,
        $apartmentServicePrice,
        $customer,
        $service,
        $apartmentRepository,
        $debitLogs
    ) {
        set_time_limit(0);
        $file = $request->file('file_import');
        $path = $file->getRealPath();
        $excel_data = Excel::load($path)->get();

        storage_path('upload', $file->getClientOriginalName());

        $url = [
            'name' => $file->getClientOriginalName(),
            'uuid' => (string) \Webpatser\Uuid\Uuid::generate(),
        ];

        $cronJob = $cronJobManager->findSignatureBuildingId('phidaukydebitprocess:cron', $buildingId)->first();
        if (!$cronJob) {
            $cronJob = $cronJobManager->create([
                'building_id' => $buildingId,
                'user_id' => auth()->user()->id,
                'signature' => 'phidaukydebitprocess:cron',
                'status' => -1,
                'deadline' => $request['deadline'] ? Carbon::parse($request['deadline'])->format('Y-m-d') : Carbon::now()->format('Y-m-d')
            ]);
        }
        $cycleName = $request['deadline'] ? Carbon::parse($request['deadline'])->format('Ym') : Carbon::now()->format('Ym');
        if ($excel_data->count()) {
            $count = 0;
            foreach ($excel_data as $data) {
                $exceldataJson = json_encode($data);
                if($data->ma_can_ho == null)
                {
                    $debitLogs->create([
                        'bdc_building_id' => $buildingId,
                        'bdc_apartment_id' => $data->ma_can_ho,
                        'bdc_service_id' => $data->ma_dich_vu,
                        'key' => "import_phi_dau_ky",
                        'cycle_name' => $cycleName,
                        'input' => $exceldataJson,
                        'data' => "",
                        'message' => "Mã căn hộ không được để trống.",
                        'status' => 100
                    ]);
                    continue;
                }

                if($data->ky_thang == null)
                {
                    $debitLogs->create([
                        'bdc_building_id' => $buildingId,
                        'bdc_apartment_id' => $data->ma_can_ho,
                        'bdc_service_id' => $data->ma_dich_vu,
                        'key' => "import_phi_dau_ky",
                        'cycle_name' => $cycleName,
                        'input' => $exceldataJson,
                        'data' => "",
                        'message' => "Mã căn hộ $data->ma_can_ho không có kỳ tháng.",
                        'status' => 100
                    ]);
                    continue;
                }

                // $_apartment = $apartmentRepository->findByCode($data->ma_can_ho);
                $_apartment = Apartments::where('code', trim($data->ma_can_ho))->first();
                if($_apartment == null) {
                    $debitLogs->create([
                        'bdc_building_id' => $buildingId,
                        'bdc_apartment_id' => $data->ma_can_ho,
                        'bdc_service_id' => $data->ma_dich_vu,
                        'key' => "import_phi_dau_ky",
                        'cycle_name' => $cycleName,
                        'input' => $exceldataJson,
                        'data' => "",
                        'message' => "Mã căn hộ $data->ma_can_ho không tồn tại.",
                        'status' => 100
                    ]);
                    continue;
                }
                // $_apartmentServicePrice = $apartmentServicePrice->findApartmentUsingDienNuoc($buildingId, $_apartment->id, $data->ma_dich_vu);
                $_apartmentServicePrice = ApartmentServicePrice::where('bdc_building_id', $buildingId)
                    ->where('bdc_apartment_id', $_apartment->id)
                    ->where('bdc_service_id', $data->ma_dich_vu)
                    ->where('bdc_price_type_id', 3)->first();

                if($_apartmentServicePrice == null){
                    $debitLogs->create([
                        'bdc_building_id' => $buildingId,
                        'bdc_apartment_id' => $_apartment->id,
                        'bdc_service_id' => $data->ma_dich_vu,
                        'key' => "import_phi_dau_ky",
                        'cycle_name' => $cycleName,
                        'input' => $exceldataJson,
                        'data' => "",
                        'message' => "Căn hộ $data->ma_can_ho mã dich vụ $data->ma_dich_vu không phải loại dịch vụ đầu kỳ",
                        'status' => 101
                    ]);
                    continue;
                }
               
                // lấy chủ hộ của căn hộ
                // $_customer = UserApartments::getPurchaser($_apartment->id, 0);
                $_customer = UserApartments::getPurchaser($apartmentServicePrice->bdc_apartment_id, 0);
                if($_customer == null){
                    $debitLogs->create([
                        'bdc_building_id' => $buildingId,
                        'bdc_apartment_id' => $_apartment->id,
                        'bdc_service_id' => $data->ma_dich_vu,
                        'key' => "import_phi_dau_ky",
                        'cycle_name' => $cycleName,
                        'input' => $exceldataJson,
                        'data' => "",
                        'message' => "Căn hộ $data->ma_can_ho chưa có chủ hộ.",
                        'status' => 102
                    ]);
                    continue;
                }
                // Lấy ra chi tiết dịch vụ
                // $_service = $service->findService($data->ma_dich_vu);
                $_service = Service::where('status', 1)->where('id', trim($data->ma_dich_vu))->first();
                if($_service == null){
                    $debitLogs->create([
                        'bdc_building_id' => $buildingId,
                        'bdc_apartment_id' => $_apartment->id,
                        'bdc_service_id' => $data->ma_dich_vu,
                        'key' => "import_phi_dau_ky",
                        'cycle_name' => $cycleName,
                        'input' => $exceldataJson,
                        'data' => "",
                        'message' => "Dịch vụ $data->ma_dich_vu chưa được đưa vào hoạt động.",
                        'status' => 103
                    ]);
                    continue;
                }
                // Lấy thông tin căn hộ
                $apartment = $_apartmentServicePrice->apartment;
                $cycle = $data->ky_thang;
                $_fromDate = $request['from_date'] ? Carbon::parse($request['from_date'])->format('Y-m-d') : Carbon::now()->format('Y-m-d');
                $toDate = $request['to_date'] ? Carbon::parse($request['to_date'])->format('Y-m-d') : Carbon::now()->format('Y-m-d');
                $customerInfo = $_customer->user_info_first;
                if($customerInfo == null)
                {
                    $debitLogs->create([
                        'bdc_building_id' => $buildingId,
                        'bdc_apartment_id' => $_apartment->id,
                        'bdc_service_id' => $data->ma_dich_vu,
                        'key' => "import_phi_dau_ky",
                        'cycle_name' => $cycleName,
                        'input' => $exceldataJson,
                        'data' => "",
                        'message' => "Thông tin người dùng không tồn tại.",
                        'status' => 104
                    ]);
                    continue;
                }
                if(ctype_digit($data->ky_thang) == false && strlen((string)$data->ky_thang) !=6)
                {
                    $debitLogs->create([
                        'bdc_building_id' => $buildingId,
                        'bdc_apartment_id' => $_apartment->id,
                        'bdc_service_id' => $data->ma_dich_vu,
                        'key' => "import_phi_dau_ky",
                        'cycle_name' => $cycleName,
                        'input' => $exceldataJson,
                        'data' => "",
                        'message' => "Định dạng kỳ tháng phải là: yyyymm.",
                        'status' => 105
                    ]);
                    continue;
                }
                $_apartmentServicePrice->customer_name = $customerInfo->full_name;
                $_apartmentServicePrice->customer_address = $customerInfo->address;
                $_apartmentServicePrice->provider_address = 'test';
                $_apartmentServicePrice->deadline = $request['deadline'] ? Carbon::parse($request['deadline'])->format('Y-m-d') : Carbon::now()->format('Y-m-d');
                $_apartmentServicePrice->from_date = $_fromDate;
                $_apartmentServicePrice->to_date = $toDate;
                $_apartmentServicePrice->price =  $data->so_tien;
                $_apartmentServicePrice->free = 0;
                $_apartmentServicePrice->service_name = $_service->name;
                $_apartmentServicePrice->apartment_name = $apartment->name;
                $_apartmentServicePrice->bdc_price_type_id = 3; // phí dịch vụ
                $_apartmentServicePrice->use_bill = $request['use_bill'];
                // $cycle_name = str_replace('_', '/', $data->ky_thang);
                $cycle_name = $data->ky_thang;
                
                $_apartmentServicePrice->cycle_name = $cycle_name;

                
                
                $resultDataJson = json_encode($_apartmentServicePrice);

                $debitLogs->create([
                    'bdc_building_id' => $buildingId,
                    'bdc_apartment_id' => $_apartment->id,
                    'bdc_service_id' => $data->ma_dich_vu,
                    'key' => "import_phi_dau_ky",
                    'cycle_name' => $cycleName,
                    'input' => $exceldataJson,
                    'data' => $resultDataJson,
                    'message' => "Lấy thông tin thành công",
                    'status' => 100
                ]);

                QueueRedis::setItemForQueue('add_queue_apartment_service_phi_dau_ky_' . $buildingId, $_apartmentServicePrice);
                $count++;
            }
            if($count > 0)
            {                
                $cronJobManager->update(['status' => 0], $cronJob->id);
            }
            else
            {
                $cronJobManager->delete(['id' => $cronJob->id]);
            }            
            return true;
        }
        return false;
    }

    //xử lý công nợ chi tiết
    public function handlingDebitDetail($request, $building)
    {
        $cronJobManager = CronJobManager::where(['building_id' => $building, 'signature' => CronJobManagerRepository::DEBIT_PROCESS, 'status' => 0])->first();
        if($cronJobManager)
        {
            return false;
        }
        $cycleName = $request['cycle_year'] . $request['cycle_month'];
        if (isset($request['ids'])) {
            $firstTimeActives = array_intersect_key($request['bill_date'], $request['ids']);
            if (isset($request['frees']) || isset($request['process_again'])) {
                if(isset($request['frees']))
                {
                    $frees = array_intersect_key($firstTimeActives, $request['frees']);
                    $cans = array_keys($frees);
                }
                if(isset($request['process_again']))
                {
                    $process_again = array_intersect_key($firstTimeActives, $request['process_again']);
                    $_process_again = array_keys($process_again);
                  
                }
                $result = collect();
                foreach ($firstTimeActives as $key => $value) {
                    $result->push(['bdc_service_id' => $key, 'fist_time_active' => Carbon::parse($value)->format('Y-m-d'), 'free' => NO_FREE, 'process_again' => self::NO_PROCESS_AGAIN, 'cycle_name' => $cycleName]);
                }
                $results = [];
                foreach ($result as $key => $value) {
                    if(isset($request['frees']))
                    {
                        if (in_array($value['bdc_service_id'], $cans)) {
                            $value['free'] = FREE;
                        }
                    }
                    if(isset($request['process_again']))
                    {
                        if (in_array($value['bdc_service_id'], $_process_again)) {
                            $value['process_again'] = self::PROCESS_AGAIN;
                        }
                    }                    
                    $results[$key] = $value;
                }
                CronJobManager::create([
                    'building_id' => $building,
                    'status' => NO_STATUS,
                    'deadline' => Carbon::parse($request['payment_deadline'])->format('Y-m-d'),
                    'user_id' => \Auth::id(),
                    'signature' => 'debitprocess:cron',
                    'data' => $results,
                    'cycle_name' => $cycleName
                ]);
            } else {
                $result = collect();
                foreach ($firstTimeActives as $key => $value) {
                    $result->push(['bdc_service_id' => $key, 'fist_time_active' =>  Carbon::parse($value)->format('Y-m-d'), 'free' => NO_FREE, 'process_again' => self::NO_PROCESS_AGAIN, 'cycle_name' => $cycleName]);
                }
                CronJobManager::create([
                    'building_id' => $building,
                    'status' => NO_STATUS,
                    'deadline' => Carbon::parse($request['payment_deadline'])->format('Y-m-d'),
                    'user_id' => \Auth::id(),
                    'signature' => 'debitprocess:cron',
                    'data' => $result,
                    'cycle_name' => $cycleName
                ]);
            }
        }
        return true;
    }

    public function handlingDebitDetailYear($request, $building)
    {
        $cronJobManager = CronJobManager::where(['building_id' => $building, 'signature' => CronJobManagerRepository::DEBIT_PROCESS_YEAR, 'status' => 0])->first();
        if($cronJobManager)
        {
            return false;
        }
        $cycleName = $request['cycle_year'];
        if (isset($request['ids'])) {
            $firstTimeActives =  $request['ids'];
            if (isset($request['frees'])) {
                if(isset($request['frees']))
                {
                    $frees = array_intersect_key($firstTimeActives, $request['frees']);
                    $cans = array_flip($frees);
                }
                $result = collect();
                foreach ($firstTimeActives as $key => $value) {
                    $result->push(['bdc_service_id' => $key, 'fist_time_active' => null, 'free' => NO_FREE, 'process_again' => self::NO_PROCESS_AGAIN, 'cycle_name' => $cycleName]);
                }
                $results = [];
                foreach ($result as $key => $value) {
                    if(isset($request['frees']))
                    {
                        if (in_array($value['bdc_service_id'], $cans)) {
                            $value['free'] = FREE;
                        }
                    }                   
                    $results[$key] = $value;
                }
                CronJobManager::create([
                    'building_id' => $building,
                    'status' => NO_STATUS,
                    'deadline' => $request['payment_deadline'],
                    'user_id' => \Auth::id(),
                    'signature' => 'debitprocessyear:cron',
                    'data' => $results,
                    'cycle_name' => $cycleName
                ]);
            } else {
                $result = collect();
                foreach ($firstTimeActives as $key => $value) {
                    $result->push(['bdc_service_id' => $key, 'fist_time_active' => null, 'free' => NO_FREE, 'process_again' => self::NO_PROCESS_AGAIN, 'cycle_name' => $cycleName]);
                }
                CronJobManager::create([
                    'building_id' => $building,
                    'status' => NO_STATUS,
                    'deadline' => $request['payment_deadline'],
                    'user_id' => \Auth::id(),
                    'signature' => 'debitprocessyear:cron',
                    'data' => $result,
                    'cycle_name' => $cycleName
                ]);
            }
        }
        return true;
    }

    public function showDebitApartment($id,$perPage)
    {
        return $this->model->where('bdc_apartment_id',$id)->paginate($perPage);
    }

    public function showDebitApartmentOne($id)
    {
        return $this->model->where('bdc_apartment_id',$id)->first();
    }

    public function excelDebitShowApartment($id)
    {
        $debit = $this->model->where('bdc_apartment_id',$id)->get();
        $result = Excel::create('Công nợ căn hộ', function ($excel) use ($debit) {
            $excel->setTitle('Công nợ căn hộ');
            $excel->sheet('Công nợ căn hộ', function ($sheet) use ($debit) {
                $debits = [];
                foreach ($debit as $key => $value) {
                    $debits[] = [
                        'STT'               => ($key + 1),
                        'Căn hộ'               => $value->apartment->name,
                        'Dịch vụ'               => $value->service->name,
                        'Tổng công nợ'             => number_format($value->sumery),
                        'Đã thanh toán'        => number_format($value->paid),
                        'Còn nợ'        => number_format($value->new_sumery),
                    ];
                }
                if ($debits) {
                    $sheet->fromArray($debits);
                }
            });
        })->store('xlsx',storage_path('exports/'));
$file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
return response()->download($file)->deleteFileAfterSend(true);
             
    }

    public function findService($id,$name,$perPage)
    {
        if ($name)
        {
            $monthNow = Carbon::now()->month;
            return $this->model->WhereMonth('created_at',$monthNow)->where('bdc_apartment_id',$id)->whereHas('service',function (Builder $query) use($name) {
                $query->where('name', 'like',  '%'.$name.'%');
            })->paginate($perPage);
        }
    }

    public function findBillByDebit($perPage,$building)
    {
        $monthNow = Carbon::now()->month;
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        $bills = $this->model->with('bill')->where('bdc_building_id',$building)->whereHas('bill',function (Builder $query) use($monthNow) {
            $query->whereMonth('created_at',$monthNow);
        })->paginate($perPage);
       // dd($bills);
    }

    public function findMaxVersionByCurrentMonthVersion3($buildingId, $apartmentId)
    {
        $startDate = Carbon::now()->startOfMonth()->subMonths(1);
        $endDate = Carbon::now()->endOfMonth();

        return  DB::select(DB::raw('SELECT `bdc_building_id`, SUM(previous_owed) AS total_owed, SUM(sumery) AS sumery, title, bdc_service_id, bdc_apartment_id, id FROM (
            SELECT bdc_building_id, bdc_apartment_id, MAX(`version`), previous_owed, sumery, title, bdc_service_id, id  FROM `bdc_debit_detail` 
            WHERE (`bdc_building_id` = :buildingId) AND `bdc_debit_detail`.`deleted_at` IS NULL 
                AND `bdc_debit_detail`.`to_date` >= :startDate AND `bdc_debit_detail`.`to_date` <= :endDate AND bdc_apartment_id = :apartmentId
            GROUP BY `bdc_building_id`, `bdc_apartment_id`, previous_owed, sumery , title , bdc_service_id, id
            ) AS tb1 GROUP BY `bdc_building_id`, title, bdc_service_id, bdc_apartment_id'), ['buildingId' => $buildingId, 'startDate' => $startDate, 'endDate' => $endDate, 'apartmentId' => $apartmentId]);

    }

    public function findMaxVersionByCurrentMonthVersion4($buildingId, $services, $apartments, $request)
    {
        $dataApartments = join(",",$apartments);
        $dataServices = join(",",$services);
        $sql = "SELECT tb1.*, bdc_bills.bill_code AS bill_name, bdc_services.name as service_name, bdc_apartments.name as apartment_name FROM (
                SELECT bdc_building_id, bdc_apartment_id, bdc_service_id, bdc_bill_id, bdc_apartment_service_price_id, title, sumery, from_date, to_date,
                 new_sumery, previous_owed, paid, is_free, cycle_name, quantity, price, bdc_price_type_id, created_at, id, MAX(`version`)
                FROM `bdc_debit_detail` 
                WHERE (`bdc_building_id` = ". $buildingId ." ) AND `bdc_debit_detail`.`deleted_at` IS NULL ";
        if (count($services) > 0 && count($apartments) > 0) {
            $sqlEx = "AND `bdc_apartment_id` IN (" . $dataApartments . ") AND `bdc_service_id` IN (" . $dataServices . ")";
            $sql = $sql . $sqlEx;
        }
        if ($request->cycle_name) {
            $sql = $sql."AND `cycle_name` = '". $request->cycle_name ."'";
        }

        if ($request->bdc_bill_id) {
            $sql = $sql."AND `bdc_bill_id` = ". $request->bdc_bill_id ."";
        }
        $sqlEnd = $sql." GROUP BY `bdc_building_id`, `bdc_apartment_id`, bdc_service_id, version
                ) AS tb1 
                INNER JOIN `bdc_bills` ON `tb1`.bdc_bill_id = `bdc_bills`.id 
                INNER JOIN `bdc_services` ON `tb1`.bdc_service_id = `bdc_services`.id 
                INNER JOIN `bdc_apartments` ON `tb1`.bdc_apartment_id = `bdc_apartments`.id 
                GROUP BY tb1.bdc_building_id, tb1.title, tb1.bdc_service_id, tb1.bdc_apartment_id
                ORDER BY `created_at` DESC";
        
        return  DB::select(DB::raw($sqlEnd));
    }

    public function findMaxVersionByCurrentMonthVersion5($buildingId, $services, $apartments, $request, $perPage)
    {
        $dataApartments = join(",",$apartments);
        $dataServices = join(",",$services);
        $sql = "SELECT `bdc_debit_detail`.*, `bdc_bills`.`bill_code`, `bdc_bills`.`status`, 
                `bdc_bills`.`customer_name`, `bdc_bills`.`customer_address`, `bdc_bills`.`deadline`, `bdc_bills`.`confirm_date`, 
                `bdc_services`.`name` as `service_name`, `bdc_services`.`bill_date`, `bdc_apartments`.`name` as `apartment_name`, `bdc_apartments`.`code` as `apartment_code`, 
                `bdc_services`.`service_group`,`bdc_bills`.`created_at` as 'ngay_lap'
                FROM `bdc_debit_detail`
                INNER JOIN (
                    SELECT `bdc_bill_id`, `bdc_apartment_service_price_id`, MAX(version) as version
                    FROM `bdc_debit_detail`
                    WHERE `bdc_debit_detail`.`bdc_building_id` = $buildingId";
                if(count($apartments) > 0){
                    $sql .=" AND `bdc_debit_detail`.`bdc_apartment_id` IN ($dataApartments)";
                }
                $sql .=" AND `bdc_debit_detail`.`deleted_at` is null
                GROUP BY bdc_bill_id, bdc_apartment_service_price_id) as tb1
                on tb1.`bdc_apartment_service_price_id`=`bdc_debit_detail`.`bdc_apartment_service_price_id`
                AND tb1.`bdc_bill_id` = `bdc_debit_detail`.`bdc_bill_id`
                AND tb1.`version`=`bdc_debit_detail`.`version`
                AND `bdc_debit_detail`.`deleted_at` is null";

        if (count($services) > 0 && count($apartments) > 0) {
            $sql .= " AND `bdc_debit_detail`.`bdc_apartment_id` IN ($dataApartments) AND `bdc_debit_detail`.`bdc_service_id` IN ($dataServices) ";
        }
        if ($request->cycle_name) { 
            $sql .= " AND `cycle_name` = '". $request->cycle_name ."'";
        }
        if ($request->bdc_bill_id) {
            $sql .= " AND `bdc_debit_detail`.`bdc_bill_id` = $request->bdc_bill_id";
        }
        $sql .= " INNER JOIN `bdc_bills` ON `bdc_debit_detail`.bdc_bill_id = `bdc_bills`.id 
        INNER JOIN `bdc_services` ON `bdc_debit_detail`.bdc_service_id = `bdc_services`.id 
        INNER JOIN `bdc_apartments` ON `bdc_debit_detail`.bdc_apartment_id = `bdc_apartments`.id"; 
        if($request->bill_code)
        {
            $sql .= " AND `bdc_bills`.`bill_code` LIKE '%$request->bill_code%'";
        }
        if($request->service_group)
        {
            $sql .= " AND `bdc_services`.`service_group` = $request->service_group";
        }
        if($request->ip_place_id)
        {
            $sql .= " AND `bdc_apartments`.`building_place_id` = $request->ip_place_id";
        }
        if($request->new_sumery)
        {
            $sql .= " AND `bdc_debit_detail`.`new_sumery` > $request->new_sumery";
        }
        $sql .= " GROUP BY `bdc_debit_detail`.`id`";
        $sql .= " ORDER BY `bdc_debit_detail`.`created_at` DESC";
        return DB::table( DB::raw("($sql) as sub") )->paginate($perPage);
        //return  DB::select(DB::raw($sql));
    }
    public function findMaxVersionByCurrentMonthVersion6($buildingId, $services, $apartments, $request)
    {
        $dataApartments = join(",",$apartments);
        $dataServices = join(",",$services);
        $sql = "SELECT `bdc_debit_detail`.*, `bdc_bills`.`bill_code`, `bdc_bills`.`status`, 
                `bdc_bills`.`customer_name`, `bdc_bills`.`customer_address`, `bdc_bills`.`deadline`, `bdc_bills`.`confirm_date`, 
                `bdc_services`.`name` as `service_name`, `bdc_services`.`bill_date`, `bdc_apartments`.`name` as `apartment_name`, `bdc_apartments`.`code` as `apartment_code`, 
                `bdc_services`.`service_group`,`bdc_bills`.`created_at` as 'ngay_lap'
                FROM `bdc_debit_detail`
                INNER JOIN (
                    SELECT `bdc_bill_id`, `bdc_apartment_service_price_id`, MAX(version) as version
                    FROM `bdc_debit_detail`
                    WHERE `bdc_debit_detail`.`bdc_building_id` = $buildingId";
                if(count($apartments) > 0){
                    $sql .=" AND `bdc_debit_detail`.`bdc_apartment_id` IN ($dataApartments)";
                }
                $sql .=" AND `bdc_debit_detail`.`deleted_at` is null
                GROUP BY bdc_bill_id, bdc_apartment_service_price_id) as tb1
                on tb1.`bdc_apartment_service_price_id`=`bdc_debit_detail`.`bdc_apartment_service_price_id`
                AND tb1.`bdc_bill_id` = `bdc_debit_detail`.`bdc_bill_id`
                AND tb1.`version`=`bdc_debit_detail`.`version`
                AND `bdc_debit_detail`.`deleted_at` is null";

        if (count($services) > 0 && count($apartments) > 0) {
            $sql .= " AND `bdc_debit_detail`.`bdc_apartment_id` IN ($dataApartments) AND `bdc_debit_detail`.`bdc_service_id` IN ($dataServices) ";
        }
        if ($request->cycle_name) { 
            $sql .= " AND `cycle_name` = '". $request->cycle_name ."'";
        }
        if ($request->bdc_bill_id) {
            $sql .= " AND `bdc_debit_detail`.`bdc_bill_id` = $request->bdc_bill_id";
        }
        $sql .= " INNER JOIN `bdc_bills` ON `bdc_debit_detail`.bdc_bill_id = `bdc_bills`.id 
        INNER JOIN `bdc_services` ON `bdc_debit_detail`.bdc_service_id = `bdc_services`.id 
        INNER JOIN `bdc_apartments` ON `bdc_debit_detail`.bdc_apartment_id = `bdc_apartments`.id"; 
        if($request->bill_code)
        {
            $sql .= " AND `bdc_bills`.`bill_code` LIKE '%$request->bill_code%'";
        }
        if($request->service_group)
        {
            $sql .= " AND `bdc_services`.`service_group` = $request->service_group";
        }
        if($request->ip_place_id)
        {
            $sql .= " AND `bdc_apartments`.`building_place_id` = $request->ip_place_id";
        }
        if($request->new_sumery)
        {
            $sql .= " AND `bdc_debit_detail`.`new_sumery` > $request->new_sumery";
        }
        $sql .= " GROUP BY `bdc_debit_detail`.`id`";
        $sql .= " ORDER BY `bdc_debit_detail`.`created_at` DESC";
        return  DB::select(DB::raw($sql));
    }
    public function findMaxVersionByCurrentMonthVersionStatusNotConfirm($buildingId, $services, $apartments, $request, $perPage)
    {
        $dataApartments = join(",",$apartments);
        $dataServices = join(",",$services);

        $sql = "SELECT `bdc_debit_detail`.*, `bdc_bills`.`bill_code`, `bdc_bills`.`status`, 
                `bdc_bills`.`customer_name`, `bdc_bills`.`customer_address`, `bdc_bills`.`deadline`, `bdc_bills`.`confirm_date`, 
                `bdc_services`.`name` as `service_name`, `bdc_services`.`bill_date`, `bdc_apartments`.`name` as `apartment_name`, `bdc_apartments`.`code` as `apartment_code`, 
                `bdc_services`.`service_group`,`bdc_bills`.`created_at` as 'ngay_lap'
                FROM `bdc_debit_detail`
                INNER JOIN (
                    SELECT `bdc_bill_id`, `bdc_apartment_service_price_id`
                    FROM `bdc_debit_detail`
                    WHERE `bdc_debit_detail`.`bdc_building_id` = $buildingId";
                if(count($apartments) > 0){
                    $sql .=" AND `bdc_debit_detail`.`bdc_apartment_id` IN ($dataApartments)";
                }
                $sql .=" GROUP BY bdc_bill_id, bdc_apartment_service_price_id) as tb1
                on tb1.`bdc_apartment_service_price_id`=`bdc_debit_detail`.`bdc_apartment_service_price_id`
                AND tb1.`bdc_bill_id` = `bdc_debit_detail`.`bdc_bill_id`";

        if (count($services) > 0 && count($apartments) > 0) {
            $sql .= " AND `bdc_debit_detail`.`bdc_apartment_id` IN ($dataApartments) AND `bdc_debit_detail`.`bdc_service_id` IN ($dataServices) ";
        }
        if ($request->cycle_name) { 
            $sql .= " AND `cycle_name` = '". $request->cycle_name ."'";
        }
        if ($request->bdc_bill_id) {
            $sql .= " AND `bdc_debit_detail`.`bdc_bill_id` = $request->bdc_bill_id";
        }
        $sql .= " INNER JOIN `bdc_bills` ON `bdc_debit_detail`.bdc_bill_id = `bdc_bills`.id 
        INNER JOIN `bdc_services` ON `bdc_debit_detail`.bdc_service_id = `bdc_services`.id 
        INNER JOIN `bdc_apartments` ON `bdc_debit_detail`.bdc_apartment_id = `bdc_apartments`.id"; 
        if(isset($request->from_date) && $request->from_date !=null && isset($request->to_date) && $request->to_date !=null)
        {
            $from_date = Carbon::parse($request->from_date)->format('Y-m-d');
            $to_date   = Carbon::parse($request->to_date)->format('Y-m-d');
            $sql .= " AND `bdc_debit_detail`.`from_date` = '$from_date' AND `bdc_debit_detail`.`to_date` = '$to_date'";
        }
        if($request->bill_code)
        {
            $sql .= " AND `bdc_bills`.`bill_code` LIKE '%$request->bill_code%'";
        }
        if($request->service_group)
        {
            $sql .= " AND `bdc_services`.`service_group` = $request->service_group";
        }
        if($request->ip_place_id)
        {
            $sql .= " AND `bdc_apartments`.`building_place_id` = $request->ip_place_id";
        }
        if($request->has('new_sumery') && $request->get('new_sumery') != null)
        {
            $sql .= " AND `bdc_debit_detail`.`sumery` = $request->new_sumery";
        }
        $sql .= " GROUP BY `bdc_debit_detail`.`id`";
        $sql .= " ORDER BY `bdc_debit_detail`.`created_at` DESC";
        return DB::table( DB::raw("($sql) as sub") )->paginate($perPage);
        //return  DB::select(DB::raw($sql));
    }

    public function findMaxVersionByMonth($buildingId, $apartmentIds, $apartmentService, $month)
    {
        $startDate = Carbon::createFromDate(date('Y'), $month, 1)->subMonths(1);
        $endDate = Carbon::createFromDate(date('Y'), $month, 1)->endOfMonth();
        $dataApartments = join(",",$apartmentIds);
        $sql = "SELECT `bdc_building_id`, SUM(previous_owed) AS total_owed, SUM(sumery) AS sumery, SUM(paid) AS paid, title, bdc_service_id, bdc_apartment_id FROM (
                SELECT bdc_building_id, bdc_apartment_id, MAX(`version`), previous_owed, sumery, paid, title, bdc_service_id  FROM `bdc_debit_detail` 
                WHERE (`bdc_building_id` = ". $buildingId ." ) AND `bdc_debit_detail`.`deleted_at` IS NULL 
                    AND `bdc_debit_detail`.`from_date` >= '". $startDate ."' AND `bdc_debit_detail`.`to_date` <= '". $endDate ."'";
        if (count($apartmentIds) > 0 && count($apartmentService) > 0) {
            $sqlEx = "AND bdc_apartment_id IN (" . $dataApartments . ")";
            $sql = $sql.$sqlEx;
        }
        $sqlEnd = $sql." GROUP BY `bdc_building_id`, `bdc_apartment_id`, previous_owed, sumery, paid , title , bdc_service_id
                ) AS tb1 GROUP BY `bdc_building_id`, title, bdc_service_id, bdc_apartment_id";
        $data = DB::select(DB::raw($sqlEnd));
        $resultByApartment = [];
        $apartmentDiff = [];
        foreach ($apartmentIds as $apartmentId)
        {
            foreach ($data as $debit)
            {
                if ($debit->bdc_apartment_id == $apartmentId) {
                    $resultByApartment[$apartmentId][$debit->bdc_service_id] = [
                        'sumery' => (int)$debit->sumery,
                        'paid' => (int)$debit->sumery - (int)$debit->paid
                    ];
                }
            }
        }
        foreach ($resultByApartment as $key => $item)
        {
            $serviceWithApartment = array_keys($item);
            $serviceNotWithApartment = array_diff($apartmentService, $serviceWithApartment);
            if (count($serviceNotWithApartment) == count($apartmentService)) {
                unset($resultByApartment[$key]);
                $apartmentDiff[] = $key;
            } else {
                foreach ($serviceNotWithApartment as $value)
                {
                    $resultByApartment[$key][$value] = [
                        'sumery' => 0,
                        'paid' => 0
                    ];
                }
            }
        }
        return [
            'debits' => $resultByApartment,
            'apartmentsUseService' => array_values(array_diff($apartmentIds, $apartmentDiff)),
        ];

    }

    public function findDebitById($id)
    {
        return $this->model->find($id);
    }

    public function getDetailBillId($billId)
    {
        $debitDetails = $this->findMaxVersionByBillId($billId);
        $debitDetailsResult['vehicle'] = [];
        $debitDetailsResult['service'] = [];
        $debitDetailsResult['other'] = [];
        $debitDetailsResult['first_price'] = [];
        foreach ($debitDetails as $key => $detail)
        {
            $apartmentServicePrice = ApartmentServicePrice::with('service', 'priceType', 'vehicle', 'progressive')->find($detail->bdc_apartment_service_price_id);
            if(!$apartmentServicePrice) {
                return $debitDetailsResult;
            }
            $arrayDetail = (array) $detail;
            $arrayDetail['apartmentServicePrice'] = $apartmentServicePrice;
            if ($detail->bdc_price_type_id == 1) {
                if (@$apartmentServicePrice->vehicle) {
                    $debitDetailsResult['vehicle'][] = (object) $arrayDetail;
                } else {
                    $debitDetailsResult['service'][] = (object) $arrayDetail;
                }
            } else if($detail->bdc_price_type_id == 3){
                $debitDetailsResult['first_price'][] = (object) $arrayDetail;
            } else {
                $debitDetailsResult['other'][] = (object) $arrayDetail;
            }
        }
        return $debitDetailsResult; 
    }

    public function getCycleName()
    {
        return $this->model->select('cycle_name')->groupBy('cycle_name')->orderBy('created_at', 'DESC')->pluck('cycle_name')->toArray();
    }
    public function getCycleNameV2($buildingId)
    {
        return $this->model->where('bdc_building_id',$buildingId)->select('cycle_name')->groupBy('cycle_name')->orderBy('created_at', 'DESC')->pluck('cycle_name')->toArray();
    }

    public function updateRecord($id, $price, $previous_owed, $paid, $version, $cycle_name, $sumery,$new_sumery, $fromDate, $toDate, $quantity)
    {

        $debitDetail = $this->model->find($id);
        DebitDetail::where(['bdc_apartment_service_price_id'=>$debitDetail->bdc_apartment_service_price_id,'bdc_apartment_id'=>$debitDetail->bdc_apartment_id,'cycle_name'=>$debitDetail->cycle_name,'from_date'=>$debitDetail->from_date])->update(['cycle_name'=>$cycle_name,'from_date'=>Carbon::parse($fromDate),'to_date'=>Carbon::parse($toDate)]);
        $debitDetail->sumery =  $sumery;
        $debitDetail->new_sumery =  $new_sumery;
        $debitDetail->from_date = Carbon::parse($fromDate);
        $debitDetail->to_date = Carbon::parse($toDate);
        $debitDetail->price =  $price;
        $debitDetail->previous_owed =  $previous_owed;
        $debitDetail->paid =  $paid;
        $debitDetail->version =  $version;
        $debitDetail->cycle_name = $cycle_name;
        $debitDetail->quantity =  $quantity;
        $debitDetail->updated_at = Carbon::now();
        $debitDetail->save();

       
    }

    public function findTotalPaid($buildingId, $apartmentId, $showApartment = true)
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        if($showApartment) {
            return $this->model
                ->where([
                    'bdc_building_id' => $buildingId,
                    'bdc_apartment_id' => $apartmentId,
                ])
                // ->where(['is_free' => NO_FREE])
                ->whereBetween('to_date', [$startDate, $endDate])
                ->sum('paid');
        }else{
            return $this->model
                ->where([
                    'bdc_building_id' => $buildingId
                ])
                // ->where(['is_free' => NO_FREE])
                ->whereBetween('to_date', [$startDate, $endDate])
                ->sum('paid');
        }        
    }

    public function findServiceBetweenDate($buildingId, $apartmentId, $apartmentServicePriceId, $toDate, $fromDate)
    {
        // echo $buildingId . '-' . $apartmentId . '-' . $apartmentServicePriceId;die;
        $fromDate = isset($fromDate) ? Carbon::parse($fromDate)->format('Y-m-d') : null;
        $toDate   = isset($toDate) ? Carbon::parse($toDate)->format('Y-m-d') : null;
        return $this->model
            ->where([
                'bdc_building_id' => $buildingId,
                'bdc_apartment_id' => $apartmentId,
                'bdc_apartment_service_price_id' => $apartmentServicePriceId
            ])
            ->whereDate('to_date', '>', $fromDate)
            ->first();
    }
    public static function findServiceBetweenDate_3($buildingId, $apartmentId, $apartmentServicePriceId, $toDate, $fromDate)
    {
        // echo $buildingId . '-' . $apartmentId . '-' . $apartmentServicePriceId;die;
        return DB::table('bdc_debit_detail')
            ->where([
                'bdc_building_id' => $buildingId,
                'bdc_apartment_id' => $apartmentId,
                'bdc_apartment_service_price_id' => $apartmentServicePriceId
            ])
            ->whereDate('from_date', '<', $fromDate)
            ->whereDate('to_date', '>', $toDate)
            ->whereNull('deleted_at')
            ->first();
    }
    public static function findServiceCheckFromDate($serviceId,$apartmentId, $apartmentServicePriceId, $fromDate)
    {
        return DB::table('bdc_debit_detail')->where([
                'bdc_apartment_id' => $apartmentId,
                'bdc_service_id' => $serviceId,
                'bdc_apartment_service_price_id' => $apartmentServicePriceId,
            ])
            ->whereNull('deleted_at')
            ->whereDate('to_date', '>', $fromDate)
            ->first();
    }

    public function findServiceCheckFromDate_v2($serviceId,$apartmentId, $apartmentServicePriceId, $fromDate)
    {
        return DB::table('bdc_debit_detail')->where([
                'bdc_apartment_id' => $apartmentId,
                'bdc_service_id' => $serviceId,
                'bdc_apartment_service_price_id' => $apartmentServicePriceId,
            ])
            ->whereNull('deleted_at')
            ->whereDate('to_date', '>', $fromDate)
            ->first();
    }

    public static function checkDuplicateDebitDetailBetweenDate($buildingId, $apartmentId, $apartmentServicePriceId, $fromDate, $toDate)
    {
        // echo $buildingId . '-' . $apartmentId . '-' . $apartmentServicePriceId;die;
        $fromDate = Carbon::parse($fromDate)->format('Y-m-d');
        $toDate   = Carbon::parse($toDate)->format('Y-m-d');
        return DB::table('bdc_debit_detail')->where([
                'bdc_building_id' => $buildingId,
                'bdc_apartment_id' => $apartmentId,
                'bdc_apartment_service_price_id' => $apartmentServicePriceId
            ])
            ->whereDate('from_date', '<', $fromDate)
            ->whereDate('to_date', '>', $toDate)
            ->whereNull('deleted_at')
            ->first();
    }
	
    public static function checkDuplicateBillCycleName($apartmentId, $serviceId, $apartmentServicePriceId, $cycleName)
    {
        return DebitDetail::where([
                'bdc_apartment_id' => $apartmentId,
                'bdc_service_id' => $serviceId,
                'bdc_apartment_service_price_id' => $apartmentServicePriceId,
                'cycle_name' => $cycleName
            ])
            ->first();
    }

    public function export($buildingId, $apartmentService, $apartmentsUseService, $request)
    {
        // $bill = $this->model->where('bdc_building_id', $building)->with('apartment')->get();
        $debit_details = $this->findMaxVersionByCurrentMonthVersion6($buildingId, $apartmentService, $apartmentsUseService, $request);
        
        $result = Excel::create('Hóa đơn tổng hợp', function ($excel) use ($debit_details) {
            $excel->setTitle('Hóa đơn tổng hợp');
            $excel->sheet('Hóa đơn tổng hợp', function ($sheet) use ($debit_details) {
                // $bills = [];
                $row = 1;
                $sheet->row($row, [
                    'STT',
                    'Mã BK',
                    'Kỳ BK',
                    'Căn hộ',
                    'Mã Căn hộ',
                    'Dịch vụ',
                    'Sản phẩm',
                    'Đơn giá',
                    'SL',
                    'Thành tiền',
                    'Đã thu',
                    'Còn nợ',
                    'Ngày chốt',
                    'Ngày lập',
                    'Ngày duyệt',
                    'Thời gian'
                ]);
                foreach ($debit_details as $key => $debit) {
                    try {
                        $row++;
                        if(@$debit->new_sumery == 0) {
                            $dathu = @$debit->sumery;
                            $conno = 0;
                        } else {
                            $dathu = @$debit->sumery - @$debit->new_sumery;
                            $conno = @$debit->new_sumery;
                        }
                        $data = [
                            (string)($key + 1),
                            $debit->bill_code,
                            $debit->cycle_name,
                            $debit->apartment_name,
                            $debit->apartment_code,
                            $debit->service_name,
                            $debit->title,
                            (string)$debit->price,
                            (string)$debit->quantity,
                            (string)$debit->sumery,
                            (string)$dathu,
                            (string)$conno,
                            (string)@$debit->bill_date,
                            (string)date('d/m/Y', strtotime(@$debit->created_at)),
                            (string)date('d/m/Y', strtotime(@$debit->deadline)),
                            (string)date('d/m/Y', strtotime(@$debit->from_date)) . ' - ' . date('d/m/Y', strtotime($debit->to_date))
                        ];
                        $sheet->row($row, $data);
                    } 
                    catch (Exception $e) 
                    {
                        // dd($e->getMessage());
                    }
                    
                    // die('xx');
                    // $bills[] = [
                    //     'STT' => ($key + 1),
                    //     'Mã BK' => $debit->bill_code,
                    //     'Kỳ BK' => $debit->cycle_name,
                    //     'Căn hộ' => $debit->apartment_name,
                    //     'Dịch vụ' => $debit->service_name,
                    //     'Sản phẩm' => $debit->title,
                    //     'Đơn giá' => $debit->price,
                    //     'SL' => $debit->quantity,
                    //     'Thành tiền' => $debit->sumery,
                    //     'Dư nợ cuối kỳ' => $debit->sumery + $debit->previous_owed,
                    //     'Ngày chốt' => @$debit->bill_date,
                    //     'Ngày lập' => date('d/m/Y', strtotime(@$debit->created_at)),
                    //     'Ngày duyệt' => date('d/m/Y', strtotime(@$debit->deadline)),
                    //     'Thời gian' => date('d/m/Y', strtotime(@$debit->from_date)) . ' - ' . date('d/m/Y', strtotime($debit->to_date))
                    // ];
                }
                // if ($bills) {
                //     $sheet->fromArray($bills);
                // }
            });
        })->store('xlsx',storage_path('exports/'));
        ob_end_clean();
$file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
return response()->download($file)->deleteFileAfterSend(true);
             
    }

    public function GeneralAccountant($buildingId, $fromDate, $toDate, $apartmentId = 0, $duNoCuoiKy = 0)
    {
        $fromDate = isset($fromDate) ? Carbon::parse($fromDate)->format('Y-m-d') : null;
        $toDate   = isset($toDate) ? Carbon::parse($toDate)->format('Y-m-d') : null;
        $sql = "SELECT * FROM (
            SELECT *, (`dau_ky` + `ps_trongky` - `thanh_toan`) AS `du_no_cuoi_ky` FROM (
                SELECT bdc_apartment_id, `customer_name`, `name`, `building_place_id`, bdc_building_id, 
                COALESCE(SUM(`dau_ky`), 0) AS `dau_ky`, COALESCE(SUM(`thanh_toan`), 0) AS `thanh_toan`, COALESCE(SUM(`ps_trongky`), 0) AS `ps_trongky` FROM (
                SELECT `tbl_main`.`bdc_apartment_id`, `bdc_apartments`.`name`, `bdc_apartments`.`building_place_id`, `tbl_main`.`bdc_building_id`, `tbl_main`.`bdc_bill_id`, `bdc_bills`.`customer_name`, (
                    SELECT SUM(tb1.sumery) AS `ps_trongky` FROM (
                        SELECT `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_apartment_service_price_id`, 
                            `bdc_debit_detail`.`bdc_bill_id` , `bdc_debit_detail`.`sumery`, MAX(`version`) as `version`
                        FROM `bdc_debit_detail`
                        INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id`
                        WHERE `bdc_debit_detail`.`bdc_building_id` = $buildingId AND `bdc_bills`.`status` >= -2 
                            AND `bdc_debit_detail`.`deleted_at` IS NULL AND `version` = 0
                            AND `bdc_debit_detail`.`from_date` >= '$fromDate 00:00:00'AND `bdc_debit_detail`.`from_date` <= '$toDate 23:59:59' 
                        GROUP BY `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_apartment_service_price_id`, 
                            `bdc_debit_detail`.`bdc_bill_id`
                    ) AS tb1 WHERE tb1.`bdc_apartment_id` = `tbl_main`.`bdc_apartment_id` AND tb1.`bdc_bill_id` = `tbl_main`.`bdc_bill_id` 
                ) as `ps_trongky`
                , 
                (
                    SELECT (
                        (
                            SELECT SUM(tb1.sumery) AS `ps_trongky` FROM (
                                SELECT `tbPSTK`.`bdc_apartment_id`, `tbPSTK`.`bdc_apartment_service_price_id`, 
                                    `tbPSTK`.`bdc_bill_id`, SUM(`tbPSTK`.`sumery`) AS `sumery`
                                FROM `bdc_debit_detail` AS tbPSTK
                                INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `tbPSTK`.`bdc_bill_id`
                                WHERE `tbPSTK`.`bdc_building_id` = $buildingId AND `bdc_bills`.`status` >= -2 
                                    AND `tbPSTK`.`deleted_at` IS NULL AND `version` = 0
                                    AND `tbPSTK`.`from_date` < '$fromDate'";
                                if($apartmentId > 0)
                                {
                                    $sql .= " AND `tbPSTK`.`bdc_apartment_id` = $apartmentId";
                                }
                                $sql .= " GROUP BY `tbPSTK`.`bdc_apartment_id`, `tbPSTK`.`bdc_apartment_service_price_id`, `tbPSTK`.`bdc_bill_id`
                            ) AS tb1 ";
                            if($apartmentId == 0 || $apartmentId == null)
                            {
                                $sql .= " WHERE tb1.`bdc_apartment_id` = `tbl_main`.`bdc_apartment_id` AND tb1.`bdc_bill_id` = `tbl_main`.`bdc_bill_id` ";
                            }
                        $sql .= ")
                        -
                        (
                            SELECT SUM(tb1.paid) AS `thanh_toan` 
                            FROM `bdc_debit_detail` AS tb1
                            INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `tb1`.`bdc_bill_id`
                            WHERE `tb1`.`bdc_building_id` = $buildingId AND `bdc_bills`.`status` >= -2 AND `tb1`.`deleted_at` IS NULL
                                AND `tb1`.`updated_at` < '$fromDate' ";
                            if($apartmentId > 0)
                            {
                                $sql .= " AND `tb1`.`bdc_apartment_id` = $apartmentId";
                            }
                            else
                            {
                                $sql .= " AND tb1.`bdc_apartment_id` = `tbl_main`.`bdc_apartment_id` AND tb1.`bdc_bill_id` = `tbl_main`.`bdc_bill_id`";
                            }
                        $sql .= ")
                    ) AS tbl1
                ) AS `dau_ky`
                , 
                (
                    SELECT SUM(tb1.paid) AS `thanh_toan` 
                    FROM `bdc_debit_detail` as tb1
                    INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `tb1`.`bdc_bill_id`
                    WHERE `tb1`.`bdc_building_id` = $buildingId AND `bdc_bills`.`status` >= -2
                        AND tb1.`bdc_apartment_id` = `tbl_main`.`bdc_apartment_id` 
                        AND tb1.`bdc_bill_id` = `tbl_main`.`bdc_bill_id`
                        AND `tb1`.`updated_at` >= '$fromDate 00:00:00'
                        AND `tb1`.`updated_at` <= '$toDate 23:59:59'
                        AND `tb1`.`deleted_at` IS NULL
                ) as `thanh_toan`
                FROM `bdc_debit_detail` AS tbl_main
                INNER JOIN `bdc_apartments` ON `bdc_apartments`.`id` = `tbl_main`.`bdc_apartment_id`
                INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `tbl_main`.`bdc_bill_id` 
                INNER JOIN `bdc_services` ON `bdc_services`.`id` = `tbl_main`.`bdc_service_id` 
                WHERE `tbl_main`.`bdc_building_id` = $buildingId AND `bdc_bills`.`status` >= -2 AND `tbl_main`.`deleted_at` IS NULL";
            // $sql .= " AND `bdc_debit_detail`.`updated_at` >= '$fromDate 00:00:00' AND `bdc_debit_detail`.`updated_at` <= '$toDate 23:59:59'";
        if($apartmentId > 0)
        {
            $sql .= " AND `tbl_main`.`bdc_apartment_id` = $apartmentId";
        }
        $sql .= " GROUP BY `tbl_main`.`bdc_apartment_id`, `tbl_main`.`bdc_building_id`, `tbl_main`.`bdc_bill_id` 
                    ORDER BY `tbl_main`.`bdc_apartment_id` ASC
                ) AS tbl_fn GROUP BY bdc_apartment_id, `customer_name`, `name`, `building_place_id`, bdc_building_id
            ) AS tbl_m
        ) AS tbl_x WHERE 1=1";
        if($duNoCuoiKy > 0)
        {
            $sql .= " AND `tbl_x`.`du_no_cuoi_ky` > $duNoCuoiKy";
        }
        if($apartmentId > 0)
        {
            $sql .= " LIMIT 1";
        }
        return  DB::select(DB::raw($sql));
        
    }

    public function GeneralAccountants($buildingId, $fromDate, $toDate, $apartmentIds = null)
    {
        $fromDate = isset($fromDate) ? Carbon::parse($fromDate)->format('Y-m-d') : null;
        $toDate   = isset($toDate) ? Carbon::parse($toDate)->format('Y-m-d') : null;
        $sql = "SELECT `bdc_apartment_id`, `name`, `bdc_building_id`, SUM(`ps_trongky`) AS `ps_trongky`, `dau_ky` AS `dau_ky`, SUM(`thanh_toan`) AS `thanh_toan` FROM (
            SELECT `bdc_debit_detail`.`bdc_apartment_id`, `bdc_apartments`.`name`, `bdc_debit_detail`.`bdc_building_id`, `bdc_debit_detail`.`bdc_bill_id`, (
                SELECT SUM(tb1.sumery) AS `ps_trongky` FROM (
                    SELECT `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_apartment_service_price_id`, 
                        `bdc_debit_detail`.`bdc_bill_id`, `bdc_debit_detail`.`sumery`, MAX(`version`) as `version`
                    FROM `bdc_debit_detail`
                    INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id`
                    WHERE `bdc_debit_detail`.`bdc_building_id` = $buildingId AND `bdc_bills`.`status` > 0
                        AND `bdc_debit_detail`.`from_date` >= '$fromDate'AND `bdc_debit_detail`.`from_date` <= '$toDate 23:59:59' AND `bdc_debit_detail`.`deleted_at` IS NULL
                    GROUP BY `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_apartment_service_price_id`, 
                        `bdc_debit_detail`.`bdc_bill_id`, `bdc_debit_detail`.`sumery`, `bdc_debit_detail`.`bdc_bill_id`
                ) AS tb1 WHERE tb1.`bdc_apartment_id` = `bdc_debit_detail`.`bdc_apartment_id` AND tb1.`bdc_bill_id` = `bdc_debit_detail`.`bdc_bill_id` AND `bdc_debit_detail`.`deleted_at` IS NULL
            ) as `ps_trongky`
            , 
            (
                SELECT (
                    (
                        SELECT SUM(tb1.sumery) AS `ps_trongky` FROM (
                            SELECT `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_apartment_service_price_id`, 
                                `bdc_debit_detail`.`bdc_bill_id`, `bdc_debit_detail`.`sumery`, MAX(`version`) as `version`
                            FROM `bdc_debit_detail`
                            INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id`
                            WHERE `bdc_debit_detail`.`bdc_building_id` = $buildingId AND `bdc_bills`.`status` > 0
                                AND `bdc_debit_detail`.`from_date` < '$fromDate' AND `bdc_debit_detail`.`deleted_at` IS NULL";
                            if($apartmentIds != null)
                            {
                                $sql .= " AND `bdc_debit_detail`.`bdc_apartment_id` in ($apartmentIds)";
                            }
                            $sql .= " GROUP BY `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_apartment_service_price_id`, 
                                `bdc_debit_detail`.`bdc_bill_id`, `bdc_debit_detail`.`sumery`, `bdc_debit_detail`.`bdc_bill_id`
                        ) AS tb1 ";
                        if($apartmentIds == null)
                        {
                            $sql .= " WHERE tb1.`bdc_apartment_id` = `bdc_debit_detail`.`bdc_apartment_id`";
                        }
                    $sql .= ")
                    -
                    (
                        SELECT SUM(tb1.paid) AS `thanh_toan` 
                        FROM `bdc_debit_detail` AS tb1
                        INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `tb1`.`bdc_bill_id`
                        WHERE `tb1`.`bdc_building_id` = $buildingId 
                            AND `tb1`.`updated_at` < '$fromDate' AND `tb1`.`deleted_at` IS NULL ";
                        if($apartmentIds != 0)
                        {
                            $sql .= " AND `tb1`.`bdc_apartment_id` in ($apartmentIds)";
                        }
                        else
                        {
                            $sql .= " AND tb1.`bdc_apartment_id` = `bdc_debit_detail`.`bdc_apartment_id` ";
                        }
                    $sql .= ")
                ) AS tbl1
            ) AS `dau_ky`
            , 
            (
                SELECT SUM(tb1.paid) AS `thanh_toan` 
                FROM `bdc_debit_detail` as tb1
                INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `tb1`.`bdc_bill_id`
                WHERE `tb1`.`bdc_building_id` = $buildingId AND `bdc_bills`.`status` > 0
                    AND tb1.`bdc_apartment_id` = `bdc_debit_detail`.`bdc_apartment_id` 
                    AND tb1.`bdc_bill_id` = `bdc_debit_detail`.`bdc_bill_id`
                    AND `tb1`.`updated_at` >= '$fromDate'
                    AND `tb1`.`updated_at` <= '$toDate'
                    AND `tb1`.`deleted_at` IS NULL 
            ) as `thanh_toan`
            FROM `bdc_debit_detail`
            INNER JOIN `bdc_apartments` ON `bdc_apartments`.`id` = `bdc_debit_detail`.`bdc_apartment_id`
            INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id` AND `bdc_bills`.`status` > 0
            WHERE `bdc_debit_detail`.`bdc_building_id` = $buildingId AND `bdc_debit_detail`.`deleted_at` IS NULL";
                //AND `bdc_debit_detail`.`created_at` >= '$fromDate'AND `bdc_debit_detail`.`created_at` <= '$toDate 23:59:59'";
        if($apartmentIds != null)
        {
            $sql .= " AND `bdc_debit_detail`.`bdc_apartment_id` in ($apartmentIds)";
        }
        $sql .= " GROUP BY `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_building_id`, `bdc_debit_detail`.`bdc_bill_id` 
            ORDER BY `bdc_debit_detail`.`bdc_apartment_id` ASC
        ) AS tbl_fn GROUP BY `bdc_apartment_id`, `bdc_building_id`";
        return  DB::select(DB::raw($sql));
    }


    public function Test()
    {
        return $this->model->select('bdc_debit_detail.*')
            ->join('bdc_bills', 'bdc_bills.id', '=', 'bdc_debit_detail.bdc_bill_id')
            ->where('bdc_debit_detail.bdc_building_id', 37)
            ->where('bdc_bills.status', '>', 0)
            ->get();
        // $sql = 'SELECT `bdc_debit_detail`.* FROM `bdc_debit_detail`
        // INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id`
        // WHERE `bdc_debit_detail`.`bdc_building_id`=37';
        // return DB::select(DB::raw($sql));
    }

    public function TongDauKy($buildingId, $fromDate, $apartmentId = 0)
    {
        $fromDate = isset($fromDate) ? Carbon::parse($fromDate)->format('Y-m-d') : null;
        if($apartmentId > 0)
        {
            $sql = "SELECT (
                (
                        SELECT SUM(tb1.sumery) AS `ps_trongky` FROM (
                                SELECT `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_apartment_service_price_id`, 
                                        `bdc_debit_detail`.`bdc_bill_id`, `bdc_debit_detail`.`sumery`, MAX(`version`) as `version`
                                FROM `bdc_debit_detail`
                                INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id`
                                WHERE `bdc_debit_detail`.`bdc_building_id` = $buildingId AND `bdc_bills`.`status` >= -2
                                        AND `bdc_debit_detail`.`bdc_apartment_id` = $apartmentId AND `bdc_debit_detail`.`deleted_at` is null
                                GROUP BY `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_apartment_service_price_id`, 
                                        `bdc_debit_detail`.`bdc_bill_id`, `bdc_debit_detail`.`sumery`, `bdc_debit_detail`.`bdc_bill_id`
                        ) AS tb1
                )
                -
                (
                        SELECT SUM(tb1.paid) AS `thanh_toan` 
                        FROM `bdc_debit_detail` AS tb1
                        INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `tb1`.`bdc_bill_id`
                        WHERE `tb1`.`bdc_building_id` = $buildingId 
                            AND `tb1`.`bdc_apartment_id` = $apartmentId AND `tb1`.`deleted_at` is null
                )
            ) AS `dau_ky`";
        }
        else if ($fromDate!= null)
        {
            $sql = "SELECT (
                (
                        SELECT SUM(tb1.sumery) AS `ps_trongky` FROM (
                                SELECT `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_apartment_service_price_id`, 
                                        `bdc_debit_detail`.`bdc_bill_id`, `bdc_debit_detail`.`sumery`, MAX(`version`) as `version`
                                FROM `bdc_debit_detail`
                                INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id`
                                WHERE `bdc_debit_detail`.`bdc_building_id` = $buildingId AND `bdc_bills`.`status` >= -2
                                        AND `bdc_debit_detail`.`created_at` < '$fromDate'AND `bdc_debit_detail`.`deleted_at` is null
                                GROUP BY `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_apartment_service_price_id`, 
                                        `bdc_debit_detail`.`bdc_bill_id`, `bdc_debit_detail`.`sumery`, `bdc_debit_detail`.`bdc_bill_id`
                        ) AS tb1
                )
                -
                (
                        SELECT SUM(tb1.paid) AS `thanh_toan` 
                        FROM `bdc_debit_detail` AS tb1
                        INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `tb1`.`bdc_bill_id`
                        WHERE `tb1`.`bdc_building_id` = $buildingId 
                                AND `tb1`.`updated_at` < '$fromDate'AND `tb1`.`deleted_at` is null
                )
            ) AS `dau_ky`";
        }
        else
        {
            $sql = "SELECT (
                (
                        SELECT SUM(tb1.sumery) AS `ps_trongky` FROM (
                                SELECT `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_apartment_service_price_id`, 
                                        `bdc_debit_detail`.`bdc_bill_id`, `bdc_debit_detail`.`sumery`, MAX(`version`) as `version`
                                FROM `bdc_debit_detail`
                                INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id`
                                WHERE `bdc_debit_detail`.`bdc_building_id` = $buildingId AND `bdc_bills`.`status` >= -2 AND `bdc_debit_detail`.`deleted_at` is null
                                GROUP BY `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_apartment_service_price_id`, 
                                        `bdc_debit_detail`.`bdc_bill_id`, `bdc_debit_detail`.`sumery`, `bdc_debit_detail`.`bdc_bill_id`
                        ) AS tb1
                )
                -
                (
                        SELECT SUM(tb1.paid) AS `thanh_toan` 
                        FROM `bdc_debit_detail` AS tb1
                        INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `tb1`.`bdc_bill_id`
                        WHERE `tb1`.`bdc_building_id` = $buildingId AND `tb1`.`deleted_at` is null
                )
            ) AS `dau_ky`";
        }
        return  DB::select(DB::raw($sql));
    }

    public function GeneralAccountantApartment($buildingId, $apartmentId = 0, $duNoCuoiKy = 0)
    {
        $sql = "SELECT * FROM (
            SELECT *, (`dau_ky` + `ps_trongky` - `thanh_toan`) AS `du_no_cuoi_ky` FROM (
                SELECT bdc_apartment_id, `customer_name`, `name`, `building_place_id`, bdc_building_id, 
                    COALESCE(SUM(`dau_ky`), 0) AS `dau_ky`, COALESCE(SUM(`thanh_toan`), 0) AS `thanh_toan`, COALESCE(SUM(`ps_trongky`), 0) AS `ps_trongky` FROM (
                    SELECT `bdc_debit_detail`.`bdc_apartment_id`, `bdc_apartments`.`building_place_id`, `bdc_apartments`.`name`, `bdc_debit_detail`.`bdc_building_id`, `bdc_debit_detail`.`bdc_bill_id`, `bdc_bills`.`customer_name`, (
                        SELECT SUM(tb1.sumery) AS `ps_trongky` FROM (
                            SELECT `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_apartment_service_price_id`, 
                                `bdc_debit_detail`.`bdc_bill_id`, SUM(`bdc_debit_detail`.`sumery`) AS `sumery`
                            FROM `bdc_debit_detail`
                            INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id`
                            WHERE `bdc_debit_detail`.`bdc_building_id` = $buildingId AND `bdc_bills`.`status` >= -2  AND `bdc_debit_detail`.`deleted_at` IS NULL AND `version` = 0
                            GROUP BY `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_apartment_service_price_id`, 
                                `bdc_debit_detail`.`bdc_bill_id`, `bdc_debit_detail`.`bdc_bill_id`
                        ) AS tb1 WHERE tb1.`bdc_apartment_id` = `bdc_debit_detail`.`bdc_apartment_id` AND tb1.`bdc_bill_id` = `bdc_debit_detail`.`bdc_bill_id` AND `bdc_debit_detail`.`deleted_at` IS NULL
                    ) as `ps_trongky`
                    , 
                    (
                        0
                    ) as `dau_ky`
                    , 
                    (
                        SELECT SUM(tb1.paid) AS `thanh_toan` 
                        FROM `bdc_debit_detail` as tb1
                        INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `tb1`.`bdc_bill_id`
                        WHERE `tb1`.`bdc_building_id` = $buildingId AND `bdc_bills`.`status` >= -2
                            AND tb1.`bdc_apartment_id` = `bdc_debit_detail`.`bdc_apartment_id` 
                            AND tb1.`bdc_bill_id` = `bdc_debit_detail`.`bdc_bill_id`
                            AND `tb1`.`deleted_at` IS NULL 
                    ) as `thanh_toan`
                    FROM `bdc_debit_detail`
                    INNER JOIN `bdc_apartments` ON `bdc_apartments`.`id` = `bdc_debit_detail`.`bdc_apartment_id`
                    INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id` AND `bdc_bills`.`status` >= -2
                    WHERE `bdc_debit_detail`.`bdc_building_id` = $buildingId 
                        AND `bdc_debit_detail`.`bdc_apartment_id` = $apartmentId AND `bdc_debit_detail`.`deleted_at` IS NULL
                    GROUP BY `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_building_id`, `bdc_debit_detail`.`bdc_bill_id` 
                    ORDER BY `bdc_debit_detail`.`bdc_apartment_id` ASC
                ) AS tbl_fn GROUP BY bdc_apartment_id
            ) AS tbl_m
        ) AS tbl_x WHERE 1=1";
        if($duNoCuoiKy > 0)
        {
            $sql .= " AND `tbl_x`.`du_no_cuoi_ky` > $duNoCuoiKy";
        }
        return  DB::select(DB::raw($sql));
    }

    public function GeneralAccountantApartments($buildingId, $apartmentIds = 0)
    {
        $sql = "SELECT `bdc_apartment_id`, `name`, `bdc_building_id`, SUM(`ps_trongky`) AS `ps_trongky`, SUM(`dau_ky`) AS `dau_ky`, SUM(`thanh_toan`) AS `thanh_toan` FROM (
            SELECT `bdc_debit_detail`.`bdc_apartment_id`, `bdc_apartments`.`name`, `bdc_debit_detail`.`bdc_building_id`, `bdc_debit_detail`.`bdc_bill_id`, (
                SELECT SUM(tb1.sumery) AS `ps_trongky` FROM (
                    SELECT `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_apartment_service_price_id`, 
                        `bdc_debit_detail`.`bdc_bill_id`, `bdc_debit_detail`.`sumery`, MAX(`version`) as `version`
                    FROM `bdc_debit_detail`
                    INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id`
                    WHERE `bdc_debit_detail`.`bdc_building_id` = $buildingId AND `bdc_bills`.`status` > 0 AND `bdc_debit_detail`.`deleted_at` IS NULL
                    GROUP BY `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_apartment_service_price_id`, 
                        `bdc_debit_detail`.`bdc_bill_id`, `bdc_debit_detail`.`sumery`, `bdc_debit_detail`.`bdc_bill_id`
                ) AS tb1 WHERE tb1.`bdc_apartment_id` = `bdc_debit_detail`.`bdc_apartment_id` AND tb1.`bdc_bill_id` = `bdc_debit_detail`.`bdc_bill_id` AND `bdc_debit_detail`.`deleted_at` IS NULL
            ) as `ps_trongky`
            , 
            (
                0
            ) as `dau_ky`
            , 
            (
                SELECT SUM(tb1.paid) AS `thanh_toan` 
                FROM `bdc_debit_detail` as tb1
                INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `tb1`.`bdc_bill_id`
                WHERE `tb1`.`bdc_building_id` = $buildingId AND `bdc_bills`.`status` > 0
                    AND tb1.`bdc_apartment_id` = `bdc_debit_detail`.`bdc_apartment_id` 
                    AND tb1.`bdc_bill_id` = `bdc_debit_detail`.`bdc_bill_id`
                    AND `tb1`.`deleted_at` IS NULL 
            ) as `thanh_toan`
            FROM `bdc_debit_detail`
            INNER JOIN `bdc_apartments` ON `bdc_apartments`.`id` = `bdc_debit_detail`.`bdc_apartment_id`
            INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id` AND `bdc_bills`.`status` > 0
            WHERE `bdc_debit_detail`.`bdc_building_id` = $buildingId 
                AND `bdc_debit_detail`.`bdc_apartment_id` in ($apartmentIds) AND `bdc_debit_detail`.`deleted_at` IS NULL
            GROUP BY `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_building_id`, `bdc_debit_detail`.`bdc_bill_id` 
            ORDER BY `bdc_debit_detail`.`bdc_apartment_id` ASC
        ) AS tbl_fn GROUP BY `bdc_apartment_id`, `bdc_building_id`";
        return  DB::select(DB::raw($sql));
    }

    public function GeneralAccountantAll($buildingId)
    {
        $sql = "SELECT *, (`dau_ky` + `ps_trongky` - `thanh_toan`) AS `du_no_cuoi_ky` FROM (
            SELECT bdc_apartment_id, `customer_name`, `name`, `building_place_id`, bdc_building_id, 
                COALESCE(SUM(`dau_ky`), 0) AS `dau_ky`, COALESCE(SUM(`thanh_toan`), 0) AS `thanh_toan`, COALESCE(SUM(`ps_trongky`), 0) AS `ps_trongky` FROM (
                SELECT `tbl_main`.`bdc_apartment_id`, `bdc_apartments`.`name`, `bdc_apartments`.`building_place_id`, `tbl_main`.`bdc_building_id`, `tbl_main`.`bdc_bill_id`, `bdc_bills`.`customer_name`, (
                    SELECT SUM(tb1.sumery) AS `ps_trongky` FROM (
                        SELECT `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_apartment_service_price_id`, 
                            `bdc_debit_detail`.`bdc_bill_id`, SUM(`bdc_debit_detail`.`sumery`) AS `sumery`
                        FROM `bdc_debit_detail`
                        INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id`
                        WHERE `bdc_debit_detail`.`bdc_building_id` =$buildingId AND `bdc_bills`.`status` >= -2 
                            AND `bdc_debit_detail`.`deleted_at` IS NULL AND `version` = 0
                        GROUP BY `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_apartment_service_price_id`, 
                            `bdc_debit_detail`.`bdc_bill_id`
                    ) AS tb1 WHERE tb1.`bdc_apartment_id` = `tbl_main`.`bdc_apartment_id` AND tb1.`bdc_bill_id` = `tbl_main`.`bdc_bill_id`
                ) as `ps_trongky`
                , 
                (
                    0
                ) as `dau_ky`
                , 
                (
                    SELECT SUM(tb1.paid) AS `thanh_toan` 
                    FROM `bdc_debit_detail` as tb1
                    INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `tb1`.`bdc_bill_id`
                    WHERE `tb1`.`bdc_building_id` = $buildingId AND `bdc_bills`.`status` >= -2
                        AND tb1.`bdc_apartment_id` = `tbl_main`.`bdc_apartment_id` 
                        AND tb1.`bdc_bill_id` = `tbl_main`.`bdc_bill_id`
                        AND `tb1`.`deleted_at` IS NULL 
                ) as `thanh_toan`
                FROM `bdc_debit_detail` AS tbl_main 
                INNER JOIN `bdc_apartments` ON `bdc_apartments`.`id` = `tbl_main`.`bdc_apartment_id`
                INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `tbl_main`.`bdc_bill_id` 
                WHERE `tbl_main`.`bdc_building_id` = $buildingId AND `bdc_bills`.`status` >= -2  AND `tbl_main`.`deleted_at` IS NULL 
                GROUP BY `tbl_main`.`bdc_apartment_id`, `tbl_main`.`bdc_building_id`, `tbl_main`.`bdc_bill_id`  
                ORDER BY `tbl_main`.`bdc_apartment_id` ASC
            ) AS tbl_fn GROUP BY bdc_apartment_id) AS tbl_m";
        return  DB::select(DB::raw($sql));
    }

    public function GeneralAccountantDuNoCuoiKy($buildingId, $duNoCuoiKy = 0)
    {
        $sql = "SELECT * FROM (
            SELECT *, (`dau_ky` + `ps_trongky` - `thanh_toan`) AS `du_no_cuoi_ky` FROM (
                SELECT bdc_apartment_id, `customer_name`, `name`, `building_place_id`, bdc_building_id, 
                    COALESCE(SUM(`dau_ky`), 0) AS `dau_ky`, COALESCE(SUM(`thanh_toan`), 0) AS `thanh_toan`, COALESCE(SUM(`ps_trongky`), 0) AS `ps_trongky` FROM (
                    SELECT `tbl_main`.`bdc_apartment_id`, `bdc_apartments`.`name`, `bdc_apartments`.`building_place_id`, `tbl_main`.`bdc_building_id`, `tbl_main`.`bdc_bill_id`, `bdc_bills`.`customer_name`, (
                        SELECT SUM(tb1.sumery) AS `ps_trongky` FROM (
                            SELECT `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_apartment_service_price_id`, 
                                `bdc_debit_detail`.`bdc_bill_id`, `bdc_debit_detail`.`sumery`, MAX(`version`) as `version`
                            FROM `bdc_debit_detail`
                            INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id`
                            WHERE `bdc_debit_detail`.`bdc_building_id` = $buildingId AND `bdc_bills`.`status` >= -2 
                                AND `bdc_debit_detail`.`deleted_at` IS NULL AND `version` = 0
                            GROUP BY `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_apartment_service_price_id`, 
                                `bdc_debit_detail`.`bdc_bill_id`
                        ) AS tb1 WHERE tb1.`bdc_apartment_id` = `tbl_main`.`bdc_apartment_id` AND tb1.`bdc_bill_id` = `tbl_main`.`bdc_bill_id`
                    ) as `ps_trongky`
                    , 
                    (
                        0
                    ) as `dau_ky`
                    , 
                    (
                        SELECT SUM(tb1.paid) AS `thanh_toan` 
                        FROM `bdc_debit_detail` as tb1
                        INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `tb1`.`bdc_bill_id`
                        WHERE `tb1`.`bdc_building_id` = $buildingId AND `bdc_bills`.`status` >= -2
                            AND tb1.`bdc_apartment_id` = `tbl_main`.`bdc_apartment_id` 
                            AND tb1.`bdc_bill_id` = `tbl_main`.`bdc_bill_id`
                            AND `tb1`.`deleted_at` IS NULL 
                    ) as `thanh_toan`
                    FROM `bdc_debit_detail` AS tbl_main 
                    INNER JOIN `bdc_apartments` ON `bdc_apartments`.`id` = `tbl_main`.`bdc_apartment_id`
                    INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `tbl_main`.`bdc_bill_id` 
                    WHERE `tbl_main`.`bdc_building_id` = $buildingId AND `bdc_bills`.`status` >= -2  AND `tbl_main`.`deleted_at` IS NULL 
                    GROUP BY `tbl_main`.`bdc_apartment_id`, `tbl_main`.`bdc_building_id`, `tbl_main`.`bdc_bill_id`  
                    ORDER BY `tbl_main`.`bdc_apartment_id` ASC
                ) AS tbl_fn GROUP BY bdc_apartment_id
            ) AS tbl_m
        ) AS tbl_x WHERE 1=1 ";
        if($duNoCuoiKy > 0) {
            $sql .= " AND du_no_cuoi_ky > $duNoCuoiKy";
        }
        return  DB::select(DB::raw($sql));
    }

    public function GeneralAccountantDetail($buildingId, $apartmentId, $fromDate, $toDate)
    {
        $fromDate = isset($fromDate) ? Carbon::parse($fromDate)->format('Y-m-d') : null;
        $toDate   = isset($toDate) ? Carbon::parse($toDate)->format('Y-m-d') : null;
        $sql = "SELECT `bdc_debit_detail`.`bdc_apartment_id`, `bdc_apartments`.`name`, `bdc_debit_detail`.`bdc_building_id`, 
                    `bdc_debit_detail`.`bdc_service_id`, `bdc_services`.`name` AS `service_name`, (
                        SELECT SUM(tb1.sumery) AS `ps_trongky` FROM (
                            SELECT `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_apartment_service_price_id`, 
                                    `bdc_debit_detail`.`bdc_bill_id`, `bdc_debit_detail`.`sumery`, `bdc_debit_detail`.`bdc_service_id`, 
                                    MAX(`version`) as `version`
                            FROM `bdc_debit_detail`
                            INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id`
                            WHERE `bdc_debit_detail`.`bdc_building_id` = $buildingId AND `bdc_bills`.`status` > 0 AND `bdc_debit_detail`.`deleted_at` is null
                                AND `bdc_debit_detail`.`created_at` > '$fromDate'AND `bdc_debit_detail`.`created_at` < '$toDate 23:59:59'
                            GROUP BY `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_apartment_service_price_id`, 
                                    `bdc_debit_detail`.`bdc_bill_id`, `bdc_debit_detail`.`sumery`, `bdc_debit_detail`.`bdc_service_id`
                    ) AS tb1 
                    WHERE tb1.`bdc_apartment_id` = `bdc_debit_detail`.`bdc_apartment_id` 
                        AND tb1.`bdc_service_id` = `bdc_debit_detail`.`bdc_service_id`
            ) AS `ps_trongky`
            , 
            (
                SELECT (
                    (
                        SELECT SUM(tb1.sumery) AS `ps_trongky` FROM (
                            SELECT `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_apartment_service_price_id`, 
                                `bdc_debit_detail`.`bdc_bill_id`, `bdc_debit_detail`.`sumery`, `bdc_debit_detail`.`bdc_service_id`, 
                                MAX(`version`) as `version`
                            FROM `bdc_debit_detail`
                            INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id`
                            WHERE `bdc_debit_detail`.`bdc_building_id` = $buildingId AND `bdc_bills`.`status` > 0
                                AND `bdc_debit_detail`.`created_at` < '$fromDate'AND `bdc_debit_detail`.`deleted_at` is null
                            GROUP BY `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_apartment_service_price_id`, 
                                `bdc_debit_detail`.`bdc_bill_id`, `bdc_debit_detail`.`sumery`, `bdc_debit_detail`.`bdc_service_id`
                        ) AS `tb1` 
                        WHERE `tb1`.`bdc_apartment_id` = `bdc_debit_detail`.`bdc_apartment_id`
                            AND `tb1`.`bdc_service_id` = `bdc_debit_detail`.`bdc_service_id`
                        )
                        -
                        (
                            SELECT SUM(tb1.paid) AS `thanh_toan` 
                            FROM `bdc_debit_detail` AS tb1
                            INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `tb1`.`bdc_bill_id`
                            WHERE `tb1`.`bdc_building_id` = $buildingId AND `bdc_bills`.`status` > 0
                                AND `tb1`.`updated_at` < '$fromDate'
                                AND `tb1`.`bdc_apartment_id` = `bdc_debit_detail`.`bdc_apartment_id`
                                AND `tb1`.`bdc_service_id` = `bdc_debit_detail`.`bdc_service_id` AND `tb1`.`deleted_at` is null
                        )
                ) AS tbl1
            ) AS `dau_ky`
            , 
            (
                SELECT SUM(tb1.paid) AS `thanh_toan` 
                FROM `bdc_debit_detail` as `tb1`
                INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `tb1`.`bdc_bill_id`
                WHERE `tb1`.`bdc_building_id` = $buildingId AND `bdc_bills`.`status` > 0
                    AND `tb1`.`bdc_apartment_id` = `bdc_debit_detail`.`bdc_apartment_id`
                    AND `tb1`.`bdc_service_id` = `bdc_debit_detail`.`bdc_service_id`
                    AND `tb1`.`updated_at` > '$fromDate'AND `tb1`.`updated_at` < '$toDate 23:59:59'
                    AND `tb1`.`deleted_at` IS NULL 
            ) AS `thanh_toan`
            FROM `bdc_debit_detail`  
            INNER JOIN `bdc_apartments` ON `bdc_apartments`.`id` = `bdc_debit_detail`.`bdc_apartment_id`
            INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id` AND `bdc_bills`.`status` > 0
            INNER JOIN `bdc_services` ON `bdc_services`.`id` = `bdc_debit_detail`.`bdc_service_id`
            WHERE `bdc_debit_detail`.`bdc_building_id` = $buildingId AND `bdc_debit_detail`.`bdc_apartment_id` = $apartmentId AND `bdc_debit_detail`.`deleted_at` is null
                -- AND `bdc_debit_detail`.`created_at` > '$fromDate'AND `bdc_debit_detail`.`created_at` < '$toDate 23:59:59'
            GROUP BY `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_building_id`, `bdc_debit_detail`.`bdc_service_id` 
            ORDER BY `bdc_debit_detail`.`bdc_apartment_id` ASC";
           // dd($sql);
        return  DB::select(DB::raw($sql));
    }

    public function GeneralAccountantDetails($buildingId, $apartmentIds, $fromDate, $toDate)
    {

        $fromDate = isset($fromDate) ? Carbon::parse($fromDate)->format('Y-m-d') : null;
        $toDate   = isset($toDate) ? Carbon::parse($toDate)->format('Y-m-d') : null;
        $sql = "SELECT `bdc_debit_detail`.`bdc_apartment_id`, `bdc_apartments`.`name`, `bdc_debit_detail`.`bdc_building_id`, 
                    `bdc_debit_detail`.`bdc_service_id`, `bdc_services`.`name` AS `service_name`, (
                        SELECT SUM(tb1.sumery) AS `ps_trongky` FROM (
                            SELECT `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_apartment_service_price_id`, 
                                    `bdc_debit_detail`.`bdc_bill_id`, `bdc_debit_detail`.`sumery`, `bdc_debit_detail`.`bdc_service_id`, 
                                    MAX(`version`) as `version`
                            FROM `bdc_debit_detail`
                            INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id`
                            WHERE `bdc_debit_detail`.`bdc_building_id` = $buildingId AND `bdc_bills`.`status` >= -2
                                AND `bdc_debit_detail`.`from_date` >= '$fromDate'AND `bdc_debit_detail`.`from_date` < '$toDate 23:59:59' AND `bdc_debit_detail`.`deleted_at` IS NULL
                            GROUP BY `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_apartment_service_price_id`, 
                                    `bdc_debit_detail`.`bdc_bill_id`, `bdc_debit_detail`.`sumery`, `bdc_debit_detail`.`bdc_service_id`
                    ) AS tb1 
                    WHERE tb1.`bdc_apartment_id` = `bdc_debit_detail`.`bdc_apartment_id` 
                        AND tb1.`bdc_service_id` = `bdc_debit_detail`.`bdc_service_id` AND `bdc_debit_detail`.`deleted_at` IS NULL
            ) AS `ps_trongky`
            , 
            (
                SELECT (
                    (
                        SELECT SUM(tb1.sumery) AS `ps_trongky` FROM (
                            SELECT `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_apartment_service_price_id`, 
                                `bdc_debit_detail`.`bdc_bill_id`, `bdc_debit_detail`.`sumery`, `bdc_debit_detail`.`bdc_service_id`, 
                                MAX(`version`) as `version`
                            FROM `bdc_debit_detail`
                            INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id`
                            WHERE `bdc_debit_detail`.`bdc_building_id` = $buildingId AND `bdc_bills`.`status` >= -2
                                AND `bdc_debit_detail`.`from_date` < '$fromDate'AND `bdc_debit_detail`.`deleted_at` IS NULL
                            GROUP BY `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_apartment_service_price_id`, 
                                `bdc_debit_detail`.`bdc_bill_id`, `bdc_debit_detail`.`sumery`, `bdc_debit_detail`.`bdc_service_id`
                        ) AS `tb1` 
                        WHERE `tb1`.`bdc_apartment_id` = `bdc_debit_detail`.`bdc_apartment_id`
                            AND `tb1`.`bdc_service_id` = `bdc_debit_detail`.`bdc_service_id` AND `bdc_debit_detail`.`deleted_at` IS NULL
                        )
                        -
                        (
                            SELECT SUM(tb1.paid) AS `thanh_toan` 
                            FROM `bdc_debit_detail` AS tb1
                            INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `tb1`.`bdc_bill_id`
                            WHERE `tb1`.`bdc_building_id` = $buildingId AND `bdc_bills`.`status` >= -2
                                AND `tb1`.`updated_at` < '$fromDate'
                                AND `tb1`.`bdc_apartment_id` = `bdc_debit_detail`.`bdc_apartment_id`
                                AND `tb1`.`bdc_service_id` = `bdc_debit_detail`.`bdc_service_id` AND `tb1`.`deleted_at` IS NULL
                        )
                ) AS tbl1
            ) AS `dau_ky`
            , 
            (
                SELECT SUM(tb1.paid) AS `thanh_toan` 
                FROM `bdc_debit_detail` as `tb1`
                INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `tb1`.`bdc_bill_id`
                WHERE `tb1`.`bdc_building_id` = $buildingId AND `bdc_bills`.`status` >= -2
                    AND `tb1`.`bdc_apartment_id` = `bdc_debit_detail`.`bdc_apartment_id`
                    AND `tb1`.`bdc_service_id` = `bdc_debit_detail`.`bdc_service_id`
                    AND `tb1`.`updated_at` > '$fromDate'AND `tb1`.`updated_at` < '$toDate 23:59:59'
                    AND `tb1`.`deleted_at` IS NULL 
            ) AS `thanh_toan`
            FROM `bdc_debit_detail`  
            INNER JOIN `bdc_apartments` ON `bdc_apartments`.`id` = `bdc_debit_detail`.`bdc_apartment_id`
            INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id` AND `bdc_bills`.`status` >= -2
            INNER JOIN `bdc_services` ON `bdc_services`.`id` = `bdc_debit_detail`.`bdc_service_id`
            WHERE `bdc_debit_detail`.`bdc_building_id` = $buildingId AND `bdc_debit_detail`.`bdc_apartment_id` in ($apartmentIds) AND `bdc_debit_detail`.`deleted_at` IS NULL
                -- AND `bdc_debit_detail`.`created_at` > '$fromDate'AND `bdc_debit_detail`.`created_at` < '$toDate 23:59:59'
            GROUP BY `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_building_id`, `bdc_debit_detail`.`bdc_service_id` 
            ORDER BY `bdc_debit_detail`.`bdc_apartment_id` ASC";
        return  DB::select(DB::raw($sql));
    }

    public function GeneralAccountantDetailAll($buildingId, $apartmentId)
    {
        $sql = "SELECT `bdc_debit_detail`.`bdc_apartment_id`, `bdc_apartments`.`name`, `bdc_debit_detail`.`bdc_building_id`, 
                    `bdc_debit_detail`.`bdc_service_id`, `bdc_services`.`name` AS `service_name`, (
                        SELECT SUM(tb1.sumery) AS `ps_trongky` FROM (
                            SELECT `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_apartment_service_price_id`, 
                                    `bdc_debit_detail`.`bdc_bill_id`, `bdc_debit_detail`.`sumery`, `bdc_debit_detail`.`bdc_service_id`, 
                                    MAX(`version`) as `version`
                            FROM `bdc_debit_detail`
                            INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id`
                            WHERE `bdc_debit_detail`.`bdc_building_id` = $buildingId AND `bdc_bills`.`status` > 0 AND `bdc_debit_detail`.`deleted_at` is null
                            GROUP BY `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_apartment_service_price_id`, 
                                    `bdc_debit_detail`.`bdc_bill_id`, `bdc_debit_detail`.`sumery`, `bdc_debit_detail`.`bdc_service_id`
                    ) AS tb1 WHERE tb1.`bdc_apartment_id` = `bdc_debit_detail`.`bdc_apartment_id` 
                        AND tb1.`bdc_service_id` = `bdc_debit_detail`.`bdc_service_id`
            ) AS `ps_trongky`
            , 
            (
                0
            ) AS `dau_ky`
            , 
            (
                SELECT SUM(tb1.paid) AS `thanh_toan` 
                FROM `bdc_debit_detail` as tb1
                INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `tb1`.`bdc_bill_id`
                WHERE `tb1`.`bdc_building_id` = $buildingId AND `bdc_bills`.`status` > 0
                    AND tb1.`bdc_apartment_id` = `bdc_debit_detail`.`bdc_apartment_id`
                    AND tb1.`bdc_service_id` = `bdc_debit_detail`.`bdc_service_id`
                    AND `tb1`.`deleted_at` IS NULL 
            ) AS `thanh_toan`
            FROM `bdc_debit_detail`  
            INNER JOIN `bdc_apartments` ON `bdc_apartments`.`id` = `bdc_debit_detail`.`bdc_apartment_id`
            INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id` AND `bdc_bills`.`status` > 0
            INNER JOIN `bdc_services` ON `bdc_services`.`id` = `bdc_debit_detail`.`bdc_service_id`
            WHERE `bdc_debit_detail`.`bdc_building_id` = $buildingId AND `bdc_debit_detail`.`bdc_apartment_id` = $apartmentId AND `bdc_debit_detail`.`deleted_at` is null
            GROUP BY `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_building_id`, `bdc_debit_detail`.`bdc_service_id` 
            ORDER BY `bdc_debit_detail`.`bdc_apartment_id` ASC";
            
        return  DB::select(DB::raw($sql));
    }

    public function GeneralAccountantDetailAlls($buildingId, $apartmentIds)
    {
        $sql = "SELECT *, (`dau_ky` + `ps_trongky` - `thanh_toan`) AS `du_no_cuoi_ky` FROM (
            SELECT `bdc_debit_detail`.`bdc_apartment_id`, `bdc_apartments`.`name`, `bdc_debit_detail`.`bdc_building_id`, 
                    `bdc_debit_detail`.`bdc_service_id`, `bdc_services`.`name` AS `service_name`, (
                        SELECT SUM(tb1.sumery) AS `ps_trongky` FROM (
                            SELECT `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_apartment_service_price_id`, 
                                    `bdc_debit_detail`.`bdc_bill_id`, `bdc_debit_detail`.`sumery`, `bdc_debit_detail`.`bdc_service_id`, 
                                    MAX(`version`) as `version`
                            FROM `bdc_debit_detail`
                            INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id`
                            WHERE `bdc_debit_detail`.`bdc_building_id` = $buildingId AND `bdc_bills`.`status` >= -2 AND `bdc_debit_detail`.`deleted_at` IS NULL
                            GROUP BY `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_apartment_service_price_id`, 
                                    `bdc_debit_detail`.`bdc_bill_id`, `bdc_debit_detail`.`bdc_service_id`
                    ) AS tb1 WHERE tb1.`bdc_apartment_id` = `bdc_debit_detail`.`bdc_apartment_id` 
                        AND tb1.`bdc_service_id` = `bdc_debit_detail`.`bdc_service_id` AND `bdc_debit_detail`.`deleted_at` IS NULL
            ) AS `ps_trongky`
            , 
            (
                0
            ) AS `dau_ky`
            , 
            (
                SELECT SUM(tb1.paid) AS `thanh_toan` 
                FROM `bdc_debit_detail` as tb1
                INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `tb1`.`bdc_bill_id`
                WHERE `tb1`.`bdc_building_id` = $buildingId AND `bdc_bills`.`status` >= -2
                    AND tb1.`bdc_apartment_id` = `bdc_debit_detail`.`bdc_apartment_id`
                    AND tb1.`bdc_service_id` = `bdc_debit_detail`.`bdc_service_id`
                    AND `tb1`.`deleted_at` IS NULL 
            ) AS `thanh_toan`
            FROM `bdc_debit_detail`  
            INNER JOIN `bdc_apartments` ON `bdc_apartments`.`id` = `bdc_debit_detail`.`bdc_apartment_id`
            INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id` AND `bdc_bills`.`status` >= -2 AND `bdc_bills`.`deleted_at` IS NULL
            INNER JOIN `bdc_services` ON `bdc_services`.`id` = `bdc_debit_detail`.`bdc_service_id`
            WHERE `bdc_debit_detail`.`bdc_building_id` = $buildingId AND `bdc_debit_detail`.`bdc_apartment_id` in ($apartmentIds) AND `bdc_debit_detail`.`deleted_at` IS NULL
            GROUP BY `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_building_id`, `bdc_debit_detail`.`bdc_service_id` 
            ORDER BY `bdc_debit_detail`.`bdc_apartment_id` ASC
        ) AS tbl_m";
            
        return  DB::select(DB::raw($sql));
    }

    public function GeneralAccountantDetailAllsV2($buildingId, $apartmentIds)
    {
        $sql = "SELECT *, (`dau_ky` + `ps_trongky` - `thanh_toan`) AS `du_no_cuoi_ky` FROM (
            SELECT `bdc_debit_detail`.`bdc_apartment_id`, `bdc_apartments`.`name`, `bdc_debit_detail`.`bdc_building_id`, 
                    `bdc_debit_detail`.`bdc_service_id`, `bdc_services`.`name` AS `service_name`, (
                        SELECT SUM(tb1.sumery) AS `ps_trongky` FROM (
                            SELECT `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_apartment_service_price_id`, 
                                    `bdc_debit_detail`.`bdc_bill_id`, `bdc_debit_detail`.`sumery`, `bdc_debit_detail`.`bdc_service_id`, 
                                    MAX(`version`) as `version`
                            FROM `bdc_debit_detail`
                            INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id`
                            WHERE `bdc_debit_detail`.`bdc_building_id` = $buildingId AND `bdc_bills`.`status` >= -2 AND `bdc_debit_detail`.`deleted_at` IS NULL
                            GROUP BY `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_apartment_service_price_id`, 
                                    `bdc_debit_detail`.`bdc_bill_id`, `bdc_debit_detail`.`bdc_service_id`
                    ) AS tb1 WHERE tb1.`bdc_apartment_id` = `bdc_debit_detail`.`bdc_apartment_id` 
                        AND tb1.`bdc_service_id` = `bdc_debit_detail`.`bdc_service_id` AND `bdc_debit_detail`.`deleted_at` IS NULL
            ) AS `ps_trongky`
            , 
            (
                0
            ) AS `dau_ky`
            , 
            (
                SELECT SUM(tb1.paid) AS `thanh_toan` 
                FROM `bdc_debit_detail` as tb1
                INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `tb1`.`bdc_bill_id`
                WHERE `tb1`.`bdc_building_id` = $buildingId AND `bdc_bills`.`status` >= -2
                    AND tb1.`bdc_apartment_id` = `bdc_debit_detail`.`bdc_apartment_id`
                    AND tb1.`bdc_service_id` = `bdc_debit_detail`.`bdc_service_id`
                    AND `tb1`.`deleted_at` IS NULL 
            ) AS `thanh_toan`
            FROM `bdc_debit_detail`  
            INNER JOIN `bdc_apartments` ON `bdc_apartments`.`id` = `bdc_debit_detail`.`bdc_apartment_id`
            INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id` AND `bdc_bills`.`status` >= -2
            INNER JOIN `bdc_services` ON `bdc_services`.`id` = `bdc_debit_detail`.`bdc_service_id`
            WHERE `bdc_debit_detail`.`bdc_building_id` = $buildingId AND `bdc_debit_detail`.`bdc_apartment_id` in ($apartmentIds) AND `bdc_debit_detail`.`deleted_at` IS NULL
            GROUP BY `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_building_id`, `bdc_debit_detail`.`bdc_service_id` 
            ORDER BY `bdc_debit_detail`.`bdc_apartment_id` ASC
        ) AS tbl_m";
            
        return  DB::select(DB::raw($sql));
    }

    public function filterAll($buildingId, $request)
    {
        $sql = "SELECT * FROM (
            SELECT `bdc_debit_detail`.`bdc_building_id`, `bdc_debit_detail`.`bdc_apartment_id`, `bdc_apartments`.`name`, `bdc_bills`.`bill_code`,
                `bdc_debit_detail`.`sumery`, `bdc_debit_detail`.`created_at`, '0' as `cost`, '0' as receipt_code, 'type' as `type`, 
                CONCAT('Bảng kê tháng ', `bdc_debit_detail`.`cycle_name`) as `description`
            FROM `bdc_debit_detail`
            INNER JOIN (
                SELECT bdc_bill_id, bdc_apartment_id, bdc_apartment_service_price_id, MAX(version) as version
                FROM `bdc_debit_detail`
                WHERE `bdc_debit_detail`.`bdc_building_id` = $buildingId AND `bdc_debit_detail`.`deleted_at` IS NULL
                GROUP BY bdc_bill_id, bdc_apartment_id, bdc_apartment_service_price_id) as tb1
            ON tb1.`bdc_bill_id`=`bdc_debit_detail`.`bdc_bill_id` 
                AND tb1.`bdc_apartment_service_price_id`=`bdc_debit_detail`.`bdc_apartment_service_price_id`
                AND tb1.`bdc_apartment_id`=`bdc_debit_detail`.`bdc_apartment_id`
                AND tb1.`version`=`bdc_debit_detail`.`version`
            INNER JOIN bdc_apartments ON bdc_apartments.id=bdc_debit_detail.bdc_apartment_id
            INNER JOIN bdc_bills ON bdc_bills.id=bdc_debit_detail.bdc_bill_id AND bdc_bills.`status` > 0 AND bdc_bills.`deleted_at` IS NULL
            UNION ALL
            (
                SELECT `bdc_receipts`.`bdc_building_id`, `bdc_receipts`.`bdc_apartment_id`, `bdc_apartments`.`name`, '0' as `bill_code`,
                '0' as `sumery`, `bdc_receipts`.`created_at`, `bdc_receipts`.`cost`, `bdc_receipts`.`receipt_code`, `bdc_receipts`.`type`, `bdc_receipts`.`description`
                FROM bdc_receipts
                INNER JOIN bdc_apartments ON `bdc_apartments`.`id`=`bdc_receipts`.`bdc_apartment_id`
                WHERE `bdc_receipts`.`bdc_building_id` = $buildingId AND `bdc_receipts`.`deleted_at` IS NULL
            )
        ) as tb1 WHERE 1=1 ";
        
        if (isset($request["from_date"]) && isset($request["to_date"]) && $request["from_date"] != null && $request["to_date"] != null) 
        {
            $fromDate = Carbon::parse($request["from_date"])->format('Y-m-d');
            $toDate   = Carbon::parse($request["to_date"])->format('Y-m-d');
            $sql .= " AND `created_at` >= '$fromDate 00:00:00' AND `created_at` <= '$toDate 23:59:59' ";
        }
        if(isset($request["bdc_apartment_id"]) && $request["bdc_apartment_id"] != null)
        {
            $apartmentId = $request["bdc_apartment_id"];
            $sql .= " AND bdc_apartment_id = $apartmentId";
        }

        $sql .= ' ORDER BY `created_at` ASC';
        return DB::select(DB::raw($sql));
    }

    public function filterAllDauky($buildingId, $request)
    {
        $sql = "SELECT * FROM (
            SELECT `bdc_debit_detail`.`bdc_building_id`, `bdc_debit_detail`.`bdc_apartment_id`, `bdc_apartments`.`name`, `bdc_bills`.`bill_code`,
                `bdc_debit_detail`.`sumery`, `bdc_debit_detail`.`created_at`, '0' as `cost`, '0' as receipt_code, 'type' as `type`, 
                CONCAT('Bảng kê tháng ', `bdc_debit_detail`.`cycle_name`) as `description`
            FROM `bdc_debit_detail`
            INNER JOIN (
                SELECT bdc_bill_id, bdc_apartment_id, bdc_apartment_service_price_id, MAX(version) as version
                FROM `bdc_debit_detail`
                WHERE `bdc_debit_detail`.`bdc_building_id` = $buildingId AND `bdc_debit_detail`.`deleted_at` is null
                GROUP BY bdc_bill_id, bdc_apartment_id, bdc_apartment_service_price_id
            ) as tb1
            ON tb1.`bdc_bill_id`=`bdc_debit_detail`.`bdc_bill_id` 
                AND tb1.`bdc_apartment_service_price_id`=`bdc_debit_detail`.`bdc_apartment_service_price_id`
                AND tb1.`bdc_apartment_id`=`bdc_debit_detail`.`bdc_apartment_id`
                AND tb1.`version`=`bdc_debit_detail`.`version`
            INNER JOIN bdc_apartments ON bdc_apartments.id=bdc_debit_detail.bdc_apartment_id
            INNER JOIN bdc_bills ON bdc_bills.id=bdc_debit_detail.bdc_bill_id AND bdc_bills.`status` > 0
            UNION ALL
            SELECT `bdc_receipts`.`bdc_building_id`, `bdc_receipts`.`bdc_apartment_id`, `bdc_apartments`.`name`, '0' as `bill_code`,
                '0' as `sumery`, `bdc_receipts`.`created_at`, `bdc_receipts`.`cost`, `bdc_receipts`.`receipt_code`, `bdc_receipts`.`type`, `bdc_receipts`.`description`
            FROM bdc_receipts
            INNER JOIN bdc_apartments ON `bdc_apartments`.`id`=`bdc_receipts`.`bdc_apartment_id`
            WHERE `bdc_receipts`.`bdc_building_id` = $buildingId AND `bdc_receipts`.`status` = 1
        ) as tb1 WHERE 1=1 ";
        
        if (isset($request["from_date"]) && isset($request["to_date"]) && $request["from_date"] != null && $request["to_date"] != null) 
        {
            $fromDate = Carbon::parse($request["from_date"])->format('Y-m-d');
            $toDate   = Carbon::parse($request["to_date"])->format('Y-m-d');
            $sql .= " AND `created_at` < '$fromDate'";
        }
        if(isset($request["apartment_id"]) && $request["apartment_id"] != null)
        {
            $apartmentId = $request["apartment_id"];
            $sql .= " AND bdc_apartment_id = $apartmentId";
        }

        $sql .= ' ORDER BY `created_at` ASC';

        return DB::select(DB::raw($sql));
    }
    
    public function checkStatus($status,$bill_id,$deadline)
    {

            if( $status == self::WAIT_FOR_CONFIRM ) {
                return "Chờ xác nhận";
            }elseif( $status == self::WAIT_TO_SEND ) {
                return "Chờ gửi";
            }elseif( $status == self::PAYING  && date($deadline) < now()) {
                return "Quá hạn";
            }elseif( $status == self::PAYING ) {
                $bill = $this->model->where('bdc_bill_id',$bill_id)->where('new_sumery',0)->whereHas('bill', function (Builder $query) use ($status) {
                    $query->where('status', '=', $status);
                })->orderByRaw('version DESC')->first();
                if($bill){
                    return "Đã thanh toán";
                }
                return "Chờ thanh toán";
            }else{
                return "Chưa có";
            }
    }
    public function checkStatusApp($status,$bill_id,$deadline)
    {
		$bill = Bills::find($bill_id);
        $findPaid = DebitDetailRepository::findMaxVersionPaidVersion3($bill_id);
		$sumPaid = 0;
		foreach ($findPaid as $paid) {
			$sumPaid += (int) $paid->total_paid;
		}
        $findcost = DebitDetailRepository::findMaxVersionWithBillIdV2($bill_id);
        $sumCost = 0;
        foreach ($findcost as $cost) {
            $sumCost += (int) $cost->sumery;
        }
		switch ($bill->status) {
			case (-3):
				$status = 'Chờ xác nhận';
				break;
			case (-2):
                if(($sumCost - $sumPaid) == 0 || $sumPaid >= $sumCost){
                    $status = 'Đã thanh toán';
                }else{
                    $status = 'Chờ thanh toán';
                }   
				break;
			case (2):
				$status = 'Đã thanh toán';
				break;
			case (1 && date($bill->deadline) < now() && ($sumCost - @$bill->cost_free - @$sumPaid) > 0):
				$status = 'Quá hạn';
				break;
			case (1 && ($sumCost - $sumPaid) == 0 || $sumPaid >= $sumCost):
				$status = 'Đã thanh toán';
				break;
			case (1):
				$status = 'Chờ thanh toán';
				break;
			default:
				$status = 'Chưa có';
				break;
		}
		$data = [
            'cost'=> ($sumCost - $sumPaid),
            'status'=> $status
        ];
		return $data;
    }
	
	public function checkStatusAppBK($status,$bill_id,$deadline)
    {
        $bill = Bills::find($bill_id);
        $findPaid = DebitDetailRepository::findMaxVersionPaidVersion3($bill_id);
		$sumPaid = 0;
		foreach ($findPaid as $paid) {
			$sumPaid += (int) $paid->total_paid;
		}
        $findcost = DebitDetailRepository::findMaxVersionWithBillIdV2($bill_id);
        $sumCost = 0;
        foreach ($findcost as $cost) {
            $sumCost += (int) $cost->sumery;
        }
		switch ($bill->status) {
			case (-2):
                if(($sumCost - $sumPaid) == 0 || $sumPaid >= $sumCost){
                    $status = 1;// Đã thanh toán
                }else{
                    $status = 3; // Chờ thanh toán
                }  
				break;
			case (2):
				$status = 1;// Đã thanh toán
				break;
			case (1 && date($bill->deadline) < now() && ($sumCost - @$bill->cost_free - @$sumPaid) > 0):
				$status = 2;//quá hạn
				break;
			case (1 && ($sumCost - $sumPaid) == 0 || $sumPaid >= $sumCost):
				$status = 1;// Đã thanh toán
				break;
			case (1):
				$status = 3;//Chờ thanh toán
				break;
			default:
				$status = 4;//chưa có
				break;
		}
		return $status;
    }

    public function filterServiceBillIdWithVersion($buildingId, $billId, $serviceId, $version)
    {
        return $this->model->where(['bdc_building_id' => $buildingId,'bdc_bill_id' => $billId, 'bdc_service_id' => $serviceId, 'version' => $version])->first();
    }
    public function filterServiceBillIdWithVersionV2($billId, $serviceId, $bdc_apartment_service_price_id, $version)
    {
        return $this->model->where(['bdc_bill_id' => $billId, 'bdc_service_id' => $serviceId,'bdc_apartment_service_price_id' => $bdc_apartment_service_price_id, 'version' => $version])->first();
    }
    public function filterBillId($id)
    {
        return $this->model->where('bdc_bill_id',$id)->get();
    }
     public function filterBillIdApartment($bill_id,$apartment__id)
    {
        return $this->model->where(['bdc_bill_id'=>$bill_id,'bdc_apartment_id'=>$apartment__id])->get();
    }
    public function deleteAt($ids)
    {
        return $this->model->whereIn('id', $ids)->delete();
    }
    public function delDebitDetail($id)
    {
        return $this->model->where('id', $id)->delete();
    }

    public function checkBillIdVersion($billId, $serviceId, $version)
    {
        return $this->model->where(['bdc_bill_id' => $billId, 'bdc_service_id' => $serviceId])->where('version', '>', $version)->first();
    }

    public function filterBillIdVersion($billId, $serviceId, $version)
    {
        return $this->model->where(['bdc_bill_id' => $billId, 'bdc_service_id' => $serviceId, 'version' => $version]);
    }

    public function filterFromDate($buildingId, $apartmentId, $serviceId, $fromDate)
    {
        return $this->model->where(['bdc_building_id' => $buildingId, 'bdc_apartment_id' => $apartmentId, 'bdc_service_id' => $serviceId])->where('from_date', '>', $fromDate)->get();
    }

    public function deleteByBillId($buildingId, $apartmentId, $serviceId, $billId)
    {
        return $this->model->where([
            'bdc_building_id' => $buildingId, 
            'bdc_apartment_id' => $apartmentId,
            'bdc_bill_id' => $billId, 
            'bdc_service_id' => $serviceId])->delete();
    }
    public function deleteByBillIdV2($buildingId, $apartmentId, $serviceId, $billId)
    {
        return $this->model->where([
            'bdc_building_id' => $buildingId, 
            'bdc_apartment_id' => $apartmentId,
            'bdc_bill_id' => $billId, 
            'bdc_service_id' => $serviceId])->orderBy('version', 'desc')->first()->delete();
    }
    public function action($request)
    {
            $method = $request->input('method', '');
            if ($method == 'delete') {
                $del = $this->deleteAt_v1($request);
                return back()->with('success',$del['msg']);
            }
            if ($method == 'restore') {
                $check = $this->updateAt($request);
                if($check == true){
                    return back()->with('success',"khôi phục bản ghi thành công!");
                }else{
                    return back()->with('success',"khôi phục bản ghi thất bại!");
                }
               
            }
            return back();
    }
    public function updateAt($request)
    {
        $ids = $request->input('ids', []);

        // chuyển sang kiểu array
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $list_debit = [];
        $list_bill = [];
        foreach ($ids as $id) {
            $list_debit[] = (int)explode('-',$id)[0];
            $list_bill[] = (int)explode('-',$id)[1];
            $list_bill = array_unique($list_bill);
        }
        try {
            $this->model->withTrashed()->whereIn('id',$list_debit)->restore();
            Bills::withTrashed()->whereIn('id',$list_bill)->restore();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    public function deleteAt_v1($request)
    {
        $ids = $request->input('ids', []);
        // chuyển sang kiểu array
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $list_debit = [];
        $list_bill = [];
        foreach ($ids as $id) {
            $list_debit[] = (int)explode('-',$id)[0];
            $list_bill[] = (int)explode('-',$id)[1];
            $list_bill = array_unique($list_bill);
        }

        $number = $this->model->whereIn('id',$list_debit)->delete();
        foreach ($list_bill as $key => $value) {
            $count_bill = $this->model->where('bdc_bill_id',$value)->count();
            if($count_bill == 0){
                Bills::destroy($value);
            }
        }
       
        $message = [
            'error'  => 0,
            'status' => 'success',
            'msg'    => "Đã xóa $number bản ghi!",
        ];

        return $message;
    }
}
