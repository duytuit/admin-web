<?php

namespace App\Console\Commands\V2;

use App\Helpers\dBug;
use App\Models\Building\Building;
use App\Models\LockCycleName\LockCycleName;
use App\Services\SendTelegram;
use App\Util\Debug\Log;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoLockCycleName extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto_lock_cycle';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'auto khóa kỳ';

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
            $get_all_building = Building::where('status', 1)->where('day_lock_cycle_name','>',0)->get();
           // SendTelegram::SupersendTelegramMessage('Param from auto_lock_cycle : get all building :' . json_encode($get_all_building)); 
            Log::info('check_lock_cycle', json_encode($get_all_building));
            foreach ($get_all_building as $key => $value) {
                $cycle_before = Carbon::now()->subMonth(1);
                if ($value->day_lock_cycle_name == $cycle_before->day) {
                    $get_cycle = LockCycleName::where(['bdc_building_id' => $value->id, 'cycle_name' => $cycle_before->format('Ym')])->first();
                    //SendTelegram::SupersendTelegramMessage('Param from auto_lock_cycle :get_cycle :' . json_encode($get_cycle)); 
                    if (!$get_cycle) {
                        LockCycleName::create([
                            'bdc_building_id' => $value->id,
                            'cycle_name' => $cycle_before->format('Ym'),
                            'status' => 1,
                        ]);
                       // SendTelegram::SupersendTelegramMessage('LockcycleName: create :' .json_encode($ $value->id).'cyclename'.json_encode( $cycle_before->format('Ym')).'status:1' ); 
                    } else {
                        $get_cycle->status = 1;
                        $get_cycle->created_by = null;
                        $get_cycle->save();
                    }
                }
            }
           
        } catch (\Exception $e) {
            Log::info('check_lock_cycle','1_1:'.$e->getMessage().'|'.$e->getLine());
           // SendTelegram::SupersendTelegramMessage('Exception from auto_lock_cycle:' .$e->getMessage().'|'.$e->getLine()); 
        }
    }
}
