<?php

namespace App\Models\WorkDiary;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\PublicUser\Users;
use App\Models\Department\Department;
use App\Models\PublicUser\UserInfo;
use App\Traits\ActionByUser;

class WorkDiary extends Model
{
    use SoftDeletes;

    // status
    const UN_PROCESS = 0;
    const PROCESSING = 1;
    const PROCESSED = 2;
    const RE_WORK = 3;
    const CHECKED = 4;
    const DONE = 5;

    // permission
    const P_MANAGER    = 4;
    const P_SUPERVISOR = 3;
    const P_ASSIGN_TO  = 2;
    const P_CREATED_BY = 1;
    const P_OTHER      = 0;

    use ActionByUser;
    protected $table = 'bdc_building_tasks';

    protected $fillable = [
        'title', 'description', 'created_by', 'updated_by', 'assign_to', 'watchs', 'status', 'logs', 'start_at', 'end_at', 'bdc_department_id', 'bdc_maintenance_asset_id', 'bdc_request_id', 'bdc_building_id'
    ];
    
    protected $filterable = ['bdc_department_id', 'status'];
    protected $searchable = ['title', 'description', 'end_at', 'status', 'bdc_department_id'];

    public function author()
    {
        return $this->belongsTo(Users::class);
    }

    public function people_hand()
    {
        return $this->belongsTo(UserInfo::class, 'assign_to');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'bdc_department_id');
    }

    public function pub_profile()
    {
        return $this->belongsTo(UserInfo::class, 'created_by');
    }

    public function update_by()
    {
        return $this->belongsTo(UserInfo::class, 'updated_by');
    }

    public function maintenance_asset()
    {
        return $this->belongsTo('App\Models\MaintenanceAsset\MaintenanceAsset', 'bdc_maintenance_asset_id');
    }

    public function feedback()
    {
        return $this->belongsTo('App\Models\Feedback\Feedback', 'bdc_request_id');
    }

    public function scopeFilter($query, $input)
    {
        foreach ($this->searchable as $value) {
            if (isset($input[$value])) {
                $query->where($value, $input[$value]);
            }
        }

        if (isset($input['keyword'])) {
            $search = $input['keyword'];
            $query->where(function ($q) use ($search) {
                foreach ($this->searchable as $value) {
                    $q->orWhere($value, 'LIKE', '%' . $search . '%');
                }
            });
        }

        if (isset($input['date_filter'])) {
            $date_filter = explode(" - ", $input['date_filter']);
            $from_date = date("Y/m/d", strtotime(str_replace('/','-',$date_filter[0])));
            $to_date = date("Y/m/d", strtotime(str_replace('/','-',$date_filter[1])));
            $query->whereBetween('end_at', [$from_date, $to_date])->orwhereBetween('updated_at', [$from_date, $to_date]);
        }
        if (isset($input['from_date']) && isset($input['to_date'])){
            $from_date = date("Y/m/d", strtotime(str_replace('/','-',$input['from_date'])));
            $to_date = date("Y/m/d", strtotime(str_replace('/','-',$input['to_date'])));
            $query->whereBetween('end_at', [$from_date, $to_date])->orwhereBetween('updated_at', [$from_date, $to_date]);
        }
        if (isset($input['from_date']) && $input['to_date'] == null) {
            $from_date = date("Y/m/d", strtotime(str_replace('/','-',$input['from_date'])));
            $query->where('end_at','>=',$from_date)->orWhere('updated_at','>=',$from_date);
        }

        if (isset($input['to_date']) && $input['from_date'] == null) {
            $to_date = date("Y/m/d", strtotime(str_replace('/','-',$input['to_date'])));
            $query->where('end_at','<=',$to_date)->orWhere('updated_at','<=',$to_date);
        }

        return $query;
    }

    public function scopeFilterApp($query, $input)
    {
        if (isset($input['department_id'])) {
            $input['bdc_department_id'] = $input['department_id'];
        }
        foreach ($this->filterable as $value) {
            if (isset($input[$value])) {
                $query->where($value, $input[$value]);
            }
        }

        if (isset($input['keyword'])) {
            $search = $input['keyword'];
            $query->where(function ($q) use ($search) {
                foreach ($this->searchable as $value) {
                    $q->orWhere($value, 'LIKE', '%' . $search . '%');
                }
            });
        }
        return $query;
    }
}
