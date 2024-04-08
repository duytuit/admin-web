<?php

namespace App\Models\Feedback;

use App\Models\Apartments\Apartments;
use App\Models\BoCustomer;
use App\Models\Building\Building;
use App\Models\Comments\Comments;
use App\Models\PublicUser\UserInfo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Asset\AssetApartment;
use App\Traits\ActionByUser;

class Feedback extends Model
{
    use SoftDeletes;
    //
    use ActionByUser;
    protected $table = 'feedback';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'pub_user_profile_id','type', 'title', 'content', 'rating', 'attached', 'status', 'user_id', 'app_id','bdc_apartment_id','bdc_building_id','bdc_department_id',
        'start_time', 'end_time', 'full_name', 'email', 'phone', 'unit_name', 'repair_status','bdc_asset_apartment_id','new'
    ];

    protected $hidden = [];

    protected $dates = ['deleted_at'];

    public function customer()
    {
        return $this->belongsTo(BoCustomer::class, 'customer_id', 'id');
    }

    public function pubUserProfile()
    {
        return $this->belongsTo(UserInfo::class, 'pub_user_profile_id', 'id');
    }
    public function bdcApartment()
    {
        return $this->belongsTo(Apartments::class, 'bdc_apartment_id', 'id');
    }
    public function bdcBuilding()
    {
        return $this->belongsTo(Building::class, 'bdc_building_id', 'id');
    }
    public function asset_apartment()
    {
        return $this->belongsTo(AssetApartment::class, 'bdc_asset_apartment_id', 'id');
    }


    public function comments()
    {
        $where = [
            ['parent_id', '=', 0],
            ['type', '=', 'feedback'],
        ];
        return $this->hasMany(Comments::class, 'post_id', 'id')->where($where);
    }
    public function allComments()
    {
        $where = [
            ['type', '=', 'feedback'],
        ];
        return $this->hasMany(Comments::class, 'post_id', 'id')->where($where);
    }
}
