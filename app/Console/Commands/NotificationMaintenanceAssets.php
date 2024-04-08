<?php

namespace App\Console\Commands;

use App\Repositories\MaintenanceAsset\MaintenanceAssetRepository;
use App\Services\MaintenanceQueueService;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Repositories\Assets\AssetRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class NotificationMaintenanceAssets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bdc:update_maintenance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Up date maintainance of Asset before 7 days';

    /**
     * Create a new command instance.
     *
     * @return void
     */

    protected $assetRepository;
    protected $maintenanceAssetRepository;

    public function __construct(AssetRepository $assetRepository, MaintenanceAssetRepository $maintenanceAssetRepository)
    {
        parent::__construct();
        $this->assetRepository = $assetRepository;
        $this->maintenanceAssetRepository = $maintenanceAssetRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $start = microtime(true);
        $time = 0;
        while ($time <= 1) {
            $asset = MaintenanceQueueService::getItemForQueue();
            if (is_array($asset)) {
                dump($asset);
                if (array_key_exists('exception', $asset)) {
                    $exception = $asset['exception'];
                } else {
                    $exception = 0;
                }
                try {
                    if ($exception < 3) {
                        $today = Carbon::now()->startOfDay();
                        $nextWeek = Carbon::now()->startOfDay()->addWeek();
                        if ($asset) {
                            $mantainDate = Carbon::parse($asset['maintainance_date'])->startOfDay();
                            if ($mantainDate->gte($today) && $mantainDate->lte($nextWeek)) {
                                $this->checkAndSaveMaintenance($asset, $asset['maintainance_date']);

                            } elseif ($mantainDate->lte($today)) {
                                $this->checkAssetWithPeriod($asset, $today, $nextWeek);
                            }
                            $endLoop = microtime(true);
                            $timeLoop = number_format(($endLoop - $start), 2);
                            $time = $time + $timeLoop;
                            $start = $endLoop;
                        } else {
                            break;
                        }
                    }
                } catch (Exception $e) {
                    $asset['exception'] = $exception + 1;
                    Log::info('Caught exception: ', $e->getMessage(), "\n");
                    MaintenanceQueueService::setItemForQueue($asset);
                    $endLoop = microtime(true);
                    $timeLoop = number_format(($endLoop - $start), 2);
                    $time = $time + $timeLoop;
                    $start = $endLoop;
                }
            } else {
                break;
            }
        }
        echo 'This command loaded in ', $time, ' seconds';
    }

    private function checkAndSaveMaintenance($asset, $maintainanceDate)
    {
        $checkMaintenance = $this->maintenanceAssetRepository->findMaintenance($asset['id'], $maintainanceDate);
        if (!$checkMaintenance) {
            $this->maintenanceAssetRepository->create([
                'title' => 'Bảo trì ' . $asset['type']['name'] . ' ' . $asset['name'] . ' định kì',
                'maintenance_time' => $maintainanceDate,
                'asset_id' => $asset['id'],
                'status' => 0
            ]);
        }
    }

    private function checkAssetWithPeriod($asset, $today, $nextWeek)
    {
        $periodCarbon = (int)$asset['period']['carbon_fc'];
        $totalCheckTime = (int)ceil((int)$asset['using_peroid'] / $periodCarbon);

        for ($i = 1; $i <= $totalCheckTime; $i++) {
            $dateCheck = Carbon::parse($asset['maintainance_date'])->startOfDay()->addMonths($periodCarbon * $i);
            if ($dateCheck->gte($today) && $dateCheck->lte($nextWeek)) {
                $this->checkAndSaveMaintenance($asset, $dateCheck->format('Y-m-d'));
                break;
            }
        }
    }
}
