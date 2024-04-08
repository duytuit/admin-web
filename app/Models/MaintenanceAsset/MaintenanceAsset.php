<?php

namespace App\Models\MaintenanceAsset;

use App\Models\PublicUser\Users;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Asset\Asset;
use App\Traits\ActionByUser;

class MaintenanceAsset extends Model
{
    const FINISH = 1; //đã hoàn thành
    const UNFINISH = 0; //chưa hoàn thành
    const CANCEL = 2; //Đã hủy

    use SoftDeletes;
    use ActionByUser;
    protected $table='bdc_maintenance_assets';

    protected $fillable=['id','asset_id', 'maintenance_time', 'user_id', 'status', 'description', 'title'];

    protected $searchable=['title'];

    //relationship
    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }


    public function workdiary()
    {
        return $this->hasMany('App\Models\WorkDiary\WorkDiary', 'bdc_maintenance_asset_id');
    }

    public function scopeFilter($query, $input)
    {
        foreach ($this->fillable as $value) {
            if (isset($input[$value])) {
                $query->where($value, $input[$value]);
            }
        }

        if (isset($input['keyword_maintain'])) {
            $search = $input['keyword_maintain'];
            $query->where(function ($q) use ($search) {
                foreach ($this->searchable as $value) {
                    $q->orWhere($value, 'LIKE', '%' . $search . '%');
                }
            });
        }
        return $query;
    }
}
