<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\SentStatus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Traits\ActionByUser;

class SentStatusDetail extends Model
{
    use ActionByUser;
    protected $table = "bdc_sent_status_detail";

    protected $guarded = [];

    public function sentStatus()
    {
        return $this->belongsTo(SentStatus::class, 'sent_status_id', 'id');
    }

    public static function getSentStatus($sentStatusId)
    {
        $rs = Cache::store('redis')->get(env('REDIS_PREFIX') . 'sent_detail_' . $sentStatusId);

        if ($rs ) {
            return $rs;
        }
        $success = DB::table('bdc_sent_status_detail')->where([['sent_status_id', $sentStatusId], ['status', 'true']])->count();
        $fail = DB::table('bdc_sent_status_detail')->where([['sent_status_id', $sentStatusId], ['status', 'false']])->count();
        Cache::store('redis')->put(env('REDIS_PREFIX') . 'sent_detail_success_' . $sentStatusId, $success, 60 * 60 * 24);
        Cache::store('redis')->put(env('REDIS_PREFIX') . 'sent_detail_fail_' . $sentStatusId, $fail, 60 * 60 * 24);
    }
}
