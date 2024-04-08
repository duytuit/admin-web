<?php

namespace App\Models\ActivityLog;

use Illuminate\Database\Eloquent\Model;
use App\Models\PublicUser\UserInfo;
use App\Models\PublicUser\Users;
use App\Models\Building\Building;
use App\Models\Customers\Customers;
use App\Traits\ActionByUser;

class ActivityLog extends Model
{

    use ActionByUser;
    protected $table = "activity_log";

    protected $fillable = [
       'subject_id', 'causer_id', 'bdc_building_id'
    ];

    public function userInfo()
    {
        return $this->belongsTo(UserInfo::class, 'subject_id');
    }
    public function user()
    {
        return $this->belongsTo(Users::class, 'causer_id');
    }
    public function building()
    {
        return $this->belongsTo(Building::class, 'bdc_building_id');
    }
    public function customer()
    {
        return $this->belongsTo(Customers::class, 'subject_id');
    }
}
