<?php

namespace App\Console\Commands\V2;

use App\Helpers\dBug;
use App\Models\Building\Building;
use App\Models\LockCycleName\LockCycleName;
use App\Services\SendTelegram;
use App\Util\Debug\Log;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoEnableLockCycleName extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto_enable_lock_cycle';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'auto mở lại khóa kỳ';

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
    public function handle()
    {
        try {
            $mess= null;
            $today = Carbon::now()->format('Y-m-d H:i');
            $get_cycle = LockCycleName::where('schedule_active', 'like', '%' . $today . '%')->orderBy('cycle_name', 'desc')->get();
            $mess = ' Value Today:'.$today.' Lockcyclename:'.json_encode($get_cycle);
            Log::info('check_enable_lock_cycle', 'begin'.json_encode($get_cycle));
            if ($get_cycle->count() > 0) {
                SendTelegram::SupersendTelegramMessage('Param from auto_enable_lock_cycle' . json_encode($mess));
                foreach ($get_cycle as $key => $value) {
                    if ($key == 0) {
                        $get_cycle_name_previous = LockCycleName::where('cycle_name', '<', $value->cycle_name)->get();
                        SendTelegram::SupersendTelegramMessage('Param from auto_enable_lock_cycle : get_cycle_name_previous: ' . json_encode($get_cycle_name_previous)); 
                        if ($get_cycle_name_previous->count() > 0) {
                            foreach ($get_cycle_name_previous as $key_1 => $value_1) {
                                $value_1->status = 1;
                                $value_1->schedule_active = null;
                                $value_1->created_by = null;
                                $value_1->save();
                            }
                        }
                    }
                    $value->status = 1;
                    $value->schedule_active = null;
                    $value->created_by = null;
                    $value->save();
                }
            }
           
        } catch (\Exception $e) {
            Log::info('check_enable_lock_cycle','1_2:'.$e->getMessage().'|'.$e->getLine());
            SendTelegram::SupersendTelegramMessage('Exception from auto_enable_lock_cycle:' .$e->getMessage().'|'.$e->getLine()); 
        }
    }
}
