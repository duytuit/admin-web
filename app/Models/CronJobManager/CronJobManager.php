<?php

namespace App\Models\CronJobManager;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class CronJobManager extends Model
{
    use SoftDeletes;

    use ActionByUser;

    protected $table = 'cron_job_manager';

    protected $fillable = [
        'building_id', 'user_id', 'signature', 'status', 'deadline', 'data', 'cycle_name', 'group_apartment_id', 'check_all_apartment', 'apartment_ids', 'type'
    ];

    protected $casts = [
        'data' => 'array'
    ];
}
