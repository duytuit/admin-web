<?php

namespace App\Models\DepartmentStaff;

use App\Models\Department\Department;
use App\Models\PublicUser\UserInfo;
use App\Models\PublicUser\Users;
use Illuminate\Database\Eloquent\Model;
use App\Traits\ActionByUser;

class DepartmentStaff extends Model
{
    const NOT_REGENCY = 3; // không có chức vụ
    const HEAD_BUILDING = 2;
    const HEAD_DEPRATMENT = 1;
    const STAFF_DEPRATMENT = 0;
    const REGENCY = [    // chức vụ
        "0" => "nhan_vien",
        "1" => "truong_bo_phan",
        "2" => "ban_quan_ly",
        "3" => "not_regency",
    ];
    use ActionByUser;
    protected $table = 'bdc_department_staff';
    
    protected $fillable = ['bdc_department_id', 'pub_user_id', 'type', 'permission_deny'];

    public function department()
    {
        return $this->belongsTo(Department::class, 'bdc_department_id');
    }

    public function publicUser()
    {
        return $this->belongsTo(Users::class, 'pub_user_id');
    }

    public function UserInfo()
    {
        return $this->belongsTo(UserInfo::class, 'pub_user_id','pub_user_id');
    }
}
