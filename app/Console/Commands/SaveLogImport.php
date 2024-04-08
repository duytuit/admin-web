<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LogImportService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use App\Repositories\LogImport\LogImportRepository;

class SaveLogImport extends Command
{
  protected $signature = 'bdc:save_log_import';

  protected $description = 'Save log when user import';

  protected $log_import_repository;

  public function __construct(
    LogImportRepository $log_import_repository
    )
  {
    parent::__construct();
    $this->log_import_repository = $log_import_repository;
  }

  public function handle()
  {
    $start_time = time();
    $count = 0;

    try{
      do {
        $log_item = LogImportService::getItemForQueue();

        if ($log_item == NULL) {
          break;
        }

        $this->log_import_repository->create($log_item);
    } while ($log_item == NULL || ((time() - $start_time) <= 1));

    } catch(\Exception $e) {
      echo "\nERROR: " . $e->getMessage() . "\n";
      LogImportService::setItemBackQueue($log_item);
    }

  }
}