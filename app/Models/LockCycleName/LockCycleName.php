<?php

namespace App\Models\LockCycleName;

use App\Models\PublicUser\Users;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class LockCycleName extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'bdc_lock_cycle_name';

    protected $guarded = [];
    public function user_created_by()
    {
        return $this->belongsTo(Users::class, 'created_by','id');
    }

    public static function getCycleName($buildingId)
    {
        return self::where('bdc_building_id', $buildingId)->select('cycle_name')->groupBy('cycle_name')->orderBy('cycle_name', 'DESC')->pluck('cycle_name')->toArray();
    }
}
