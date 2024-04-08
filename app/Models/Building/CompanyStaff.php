<?php

namespace App\Models\Building;

use App\Models\Building\V2\Company;
use App\Models\PublicUser\Users;
use App\Models\PublicUser\UserInfo;
use Illuminate\Database\Eloquent\Model;
use App\Traits\ActionByUser;

class CompanyStaff extends Model
{
    use ActionByUser;
    protected $table = 'bdc_company_staff';

    protected $fillable = [
        'bdc_company_id', 'pub_user_id', 'type', 'name', 'code', 'email', 'phone', 'address', 'image', 'active'
    ];

    public function user()
    {
        return $this->belongsTo(Users::class, 'pub_user_id');
    }

    public function userProfile()
    {
        return $this->belongsTo(UserInfo::class, 'pub_user_id','pub_user_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'bdc_company_id');
    }
}
