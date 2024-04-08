<?php

namespace App\Models\BuildingHandbook;

use Illuminate\Database\Eloquent\Model;
use App\Models\PublicUser\Users;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\BuildingHandbookCategory\BuildingHandbookCategory;
use App\Models\PublicUser\UserInfo;
use App\Models\Department\Department;
use App\Models\BusinessPartners\BusinessPartners;
use App\Traits\ActionByUser;

class BuildingHandbook extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'bdc_handbooks';

    protected $guarded = [];

    protected $seachable = ['title', 'status', 'pub_profile_id', 'bdc_handbook_category_id', 'bdc_handbook_type_id','id','department_id','order','feature','bdc_business_partners_id','url_video'];

//    public function getDateFormat()
//    {
//        return 'Y-m-d H:i:s.u';
//    }

    public function author()
    {
        return $this->belongsTo(Users::class);
    }

    public function handbook_category()
    {
        return $this->belongsTo(BuildingHandbookCategory::class, 'bdc_handbook_category_id');
    }
    public function handbook_department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
    public function businesspartners()
    {
        return $this->belongsTo(BusinessPartners::class, 'bdc_business_partners_id');
    }

    public function pub_profile()
    {
        return $this->belongsTo(UserInfo::class, 'pub_profile_id');
    }

    public function scopeFilter($query, $input)
    {
        foreach ($this->seachable as $value) {
            if (isset($input[$value])) {
                $query->where($value, $input[$value]);
            }
        }

        if (isset($input['handbook_keyword'])) {
            $search = $input['handbook_keyword'];
            $query->where(function ($q) use ($search) {
                foreach ($this->seachable as $value) {
                    $q->orWhere($value, 'LIKE', '%' . $search . '%');
                }
            });
        }
        return $query;
    }
    
}
