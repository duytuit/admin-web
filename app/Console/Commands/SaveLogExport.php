<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LogExportService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use App\Repositories\LogExport\LogExportRepository;

class SaveLogExport extends Command
{
  protected $signature = 'bdc:save_log_export';

  protected $description = 'Save log when user export';

  protected $log_export_repository;

  public function __construct(
    LogExportRepository $log_export_repository
    )
  {
    parent::__construct();
    $this->log_export_repository = $log_export_repository;
  }

  public function handle()
  {
    $start_time = time();
    $count = 0;

    try{
      do {
        $log_item = LogExportService::getItemForQueue();

        if ($log_item == NULL) {
          break;
        }

        $this->log_export_repository->create($log_item);
    } while ($log_item == NULL || ((time() - $start_time) <= 1));

    } catch(\Exception $e) {
      echo "\nERROR: " . $e->getMessage() . "\n";
      LogExportService::setItemBackQueue($log_item);
    }

  }
}