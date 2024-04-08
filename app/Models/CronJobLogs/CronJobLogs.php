<?php

namespace App\Models\CronJobLogs;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ActionByUser;

class CronJobLogs extends Model
{
    use ActionByUser;
    protected $table = 'cron_job_logs';

    protected $fillable = [
        'bdc_building_id', 'signature', 'input_data', 'output_data', 'status'
    ];

    protected $casts = [
        'input_data' => 'array',
        'output_data' => 'array'
    ];
}
