<?php

namespace App\Models;

use App\Models\BoUser;
use App\Models\Campaign;
use App\Models\Model;
use App\Traits\MyActivityTraits;

class CampaignAssign extends Model
{
    use MyActivityTraits;

    protected $guarded = [];

    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty  = true;

    protected $casts = [
        'logs' => 'array',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class, 'campaign_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(BoUser::class, 'user_id', 'ub_id');
    }

    public function staff()
    {
        return $this->belongsTo(BoUser::class, 'staff_id', 'ub_id');
    }

    public static function count_assign_campaign($campaign_id)
    {
        $index_assigned = self::where('campaign_id', $campaign_id)
            ->where('check_diary', 1)
            ->count();
        return $index_assigned;
    }
    public static function is_exist($option)
    {
        $where = [
            ['user_id', $option['user_id']],
            ['campaign_id', $option['campaign_id']],
        ];

        if (!empty($option['cb_id'])) {
            $where[] = ['cb_id', '!=', $option['cb_id']];
        }
        $customer = self::findBy($where);

        if ($customer->count()) {
            return true;
        }

        return false;
    }
}
