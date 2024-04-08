<?php

namespace App\Jobs;

use App\Commons\Api;
use App\Util\Debug\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class createPromotionApartment implements ShouldQueue
{
    use Dispatchable;

    protected $data;
    protected $building_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $building_id)
    {
        $this->data = $data;
        $this->building_id = $building_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $result_reset = Api::POST('admin/promotion/addPromotionApartment?building_id=' . $this->building_id, $this->data);
        Log::info('check_add_promotion_apartment', json_encode($result_reset) . '||' . json_encode($this->data) . '||' . 'admin/promotion/addPromotionApartment?building_id=' . $this->building_id);
    }
}
