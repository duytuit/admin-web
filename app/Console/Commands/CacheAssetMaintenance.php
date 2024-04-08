<?php

namespace App\Console\Commands;

use App\Repositories\Assets\AssetRepository;
use App\Services\MaintenanceQueueService;
use Illuminate\Console\Command;

class CacheAssetMaintenance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bdc:cache-asset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache assets to redis';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    protected $assetRepository;

    public function __construct(AssetRepository $assetRepository)
    {
        parent::__construct();
        $this->assetRepository = $assetRepository;
    }


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $start = microtime(true);
        $limit = 100;
        $total = $this->assetRepository->count();
        $pages = (int) ceil($total/$limit);
        $i = 1;
        $offset = 0;
        while ($i <= $pages) {

            $assets = $this->assetRepository->getAssetByLimit($limit, $offset);
            foreach ($assets as $asset) {
                MaintenanceQueueService::setItemForQueue($asset->toArray());
            }
            $i++;
            $offset = ($i - 1) * $limit + 1;
        }
        $end = microtime(true);
        $time = number_format(($end - $start), 2);

        echo 'This command loaded in ', $time, ' seconds';
    }
}
