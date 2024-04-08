<?php

namespace App\Models\CustomerRatedServices;

use App\Models\Department\Department;
use App\Models\DepartmentStaff\DepartmentStaff;
use App\Models\PublicUser\UserInfo;
use App\Models\PublicUser\Users;
use App\Models\V3\User\UserInfo as UserUserInfo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class CustomerRatedServices extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'bdc_customer_rated_services';

    protected $fillable = ['customer_name', 'email', 'phone', 'apartment_name','rated','point', 'description', 'employee_id', 'department_id','bdc_building_id','created_at','user_id','from_where'];


    public function scopeFilter($query, $input)
    {
        $query->where(function ($q) use ($input) {
            foreach ($this->fillable as $value) {
                if (isset($input[$value])) {
                    $q->orWhere($value, 'LIKE', '%' . $input[$value] . '%');
                }
            }
        });

        if (isset($input['bdc_customer_rated_services_keyword'])) {
            $search = $input['bdc_customer_rated_services_keyword'];
            $query->where(function ($q) use ($search) {
                foreach ($this->fillable as $value) {
                    $q->orWhere($value, 'LIKE', '%' . $search . '%');
                }
            });
        }
        return $query;
    }
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function UserStaff()
    {
        return $this->belongsTo(DepartmentStaff::class, 'employee_id','pub_user_id');
    }
    public function user_info_rated()
    {
        return $this->belongsTo(UserInfo::class, 'employee_id','pub_user_id');
    }
    public function user()
    {
        return $this->hasOne(Users::class, 'id','employee_id');
    }
}
