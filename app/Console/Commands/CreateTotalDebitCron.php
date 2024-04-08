<?php

namespace App\Console\Commands;

use App\Models\Apartments\V2\UserApartments;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\BdcApartmentDebit\ApartmentDebitRepository;
use App\Repositories\BdcApartmentServicePrice\ApartmentServicePriceRepository;
use App\Repositories\BdcBuildingDebit\BuildingDebitRepository;
use App\Repositories\BdcDebitDetail\DebitDetailRepository;
use App\Repositories\Building\BuildingRepository;
use App\Repositories\CronJobLogs\CronJobLogsRepository;
use App\Repositories\Customers\CustomersRespository;
use App\Repositories\Service\ServiceRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CreateTotalDebitCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create_total_debit:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tính tổng công nợ của toàn nhà và căn hộ';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(
        DebitDetailRepository $debitDetail, 
        BuildingRepository $building, 
        ApartmentDebitRepository $apartmentDebit, 
        BuildingDebitRepository $buildingDebit,
        ServiceRepository $service,
        CustomersRespository $customer,
        ApartmentsRespository $apartment,
        CronJobLogsRepository $cronJobLogsRepository)
    {
        $buildings = $building->all();
        \DB::beginTransaction();
        try
        {
            foreach($buildings as $_building)
            {
                $_debitDetails = $debitDetail->findMaxVersionByCurrentMonthVersion2($_building->id, true);
                // dd($_debitDetails);
                $currentDate = Carbon::now();
                $formatDate = $currentDate->subMonths(1)->format('m/Y');
                // $title = str_slug('LÊ TRUNG KIÊN ', '-');
                foreach($_debitDetails as $_debitDetail)
                {
                    // lấy chủ hộ của căn hộ
                    $_customer = UserApartments::getPurchaser($_debitDetail->bdc_apartment_id, 0);
                    if($_customer)
                    {
                        $debitPeriodCode = $_debitDetail->bdc_building_id . '_' . $_debitDetail->bdc_apartment_id . '_' . $currentDate->format('m_Y');
                        $rsDebitPeriodCode = $apartmentDebit->findDebitPeriodCode($debitPeriodCode);
                        $totalPaid = $debitDetail->findTotalPaid($_debitDetail->bdc_building_id, $_debitDetail->bdc_apartment_id);
                        // $totalPaid = $_debitDetail->sumery - $_debitDetail->new_sumery;
                        $newOwed = $_debitDetail->sumery - $totalPaid;
                        
                        if(!$rsDebitPeriodCode)
                        {
                            $apartmentDebit->create([
                                'bdc_building_id' => $_debitDetail->bdc_building_id,
                                'bdc_apartment_id' => $_debitDetail->bdc_apartment_id,
                                'name' => $formatDate,
                                'debit_period_code' => $debitPeriodCode,
                                'old_owed' => $_debitDetail->total_owed,
                                'new_owed' => $newOwed,
                                'total' => $_debitDetail->sumery,
                                'total_paid' => $totalPaid,
                                'total_free' => $_debitDetail->sumery_free
                            ]);
                        }else{
                            $apartmentDebit->update([
                                'old_owed' => $_debitDetail->total_owed,
                                'total' => $_debitDetail->sumery,
                                'total_paid' => $totalPaid
                            ], $rsDebitPeriodCode->id);
                        }
                    }
                }
                $_debitDetails = $debitDetail->findMaxVersionByCurrentMonthVersion2($_building->id, false);
                foreach($_debitDetails as $_debitDetail)
                {
                    $debitPeriodCode = $_debitDetail->bdc_building_id . '_' . $currentDate->format('m_Y');
                    $rsDebitPeriodCode = $buildingDebit->findDebitPeriodCode($debitPeriodCode);
                    $totalPaid = $debitDetail->findTotalPaid($_debitDetail->bdc_building_id, null, false);
                    $newOwed = $_debitDetail->sumery - $totalPaid;
                    // $totalPaid = $_debitDetail->sumery - $_debitDetail->new_sumery;
                    if(!$rsDebitPeriodCode)
                    {
                        $buildingDebit->create([
                            'bdc_building_id' => $_debitDetail->bdc_building_id,
                            'name' => $formatDate,
                            'debit_period_code' => $debitPeriodCode,
                            'old_owed' => $_debitDetail->total_owed,
                            'new_owed' => $newOwed,
                            'total' => $_debitDetail->sumery,
                            'total_paid' => $totalPaid,
                            'total_free' => $_debitDetail->sumery_free
                        ]);
                    }else{
                        $buildingDebit->update([
                            'old_owed' => $_debitDetail->total_owed,
                            'total' => $_debitDetail->sumery,
                            'total_paid' => $totalPaid
                        ], $rsDebitPeriodCode->id);
                    }           
                }
            }
            \DB::commit();
        }
        catch(\Exception $e)
        {
            \DB::rollBack();
            $cronJobLogsRepository->create([
                'bdc_building_id' => 0,
                'signature' => 'create_total_debit:cron',
                'input_data' => 'Input Error',
                'output_data' => $e->getMessage(),
                'status' => 501
            ]);
        }
    }
}
