<?php

namespace App\Models\Department;

use App\Models\Building\Building;
use App\Models\Permissions\GroupPermissions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\DepartmentStaff\DepartmentStaff;
use App\Models\BuildingHandbook\BuildingHandbook;
use App\Models\Posts\Posts;
use App\Traits\ActionByUser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Department extends Model
{
    const HEAD_DEPRATMENT = 1;  // trưởng bộ phận
    const STAFF_DEPRATMENT = 0; // nhân viên
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'bdc_department';

    protected $fillable = ['name', 'bdc_building_id', 'description', 'code', 'phone', 'email', 'status', 'pub_group_id', 'status_app', 'status_notify','type_manager','Data_Type'];

    protected $searchable = ['name', 'description', 'code', 'phone', 'email'];

    public function scopeFilter($query, $input)
    {
        foreach ($this->fillable as $value) {
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

    public function department_staffs()
    {
        return $this->hasMany(DepartmentStaff::class, 'bdc_department_id');
    }
    public function department_handbooks()
    {
        return $this->hasMany(BuildingHandbook::class, 'department_id');
    }

    public function building()
    {
        return $this->belongsTo(Building::class, 'bdc_building_id');
    }

    public function head_department()
    {
        return $this->hasOne(DepartmentStaff::class, 'bdc_department_id')->where('type', self::HEAD_DEPRATMENT);
    }

    public function permissions()
    {
        return $this->belongsTo(GroupPermissions::class, 'pub_group_id');
    }
    public function posts()
    {
        return $this->hasMany(Posts::class, 'department_id', 'id');
    }
    public static function get_detail_department_by_id($id){

        $rs = Cache::store('redis')->get(env('REDIS_PREFIX') . 'get_detail_department_by_id_'.$id);
 
        if($rs){
             return $rs;
        }
        $rs = DB::table('bdc_department')->find($id); // lấy ra thông tin chủ hộ
        if(!$rs){
             return false;
        }
         Cache::store('redis')->put(env('REDIS_PREFIX') . 'get_detail_department_by_id_' . $id, $rs,60*60*24);
         
         return $rs;
     }

}
