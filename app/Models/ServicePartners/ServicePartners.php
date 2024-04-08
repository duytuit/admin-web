<?php

namespace App\Models\ServicePartners;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\BusinessPartners\BusinessPartners;
use App\Models\PublicUser\Users;
use App\Models\BuildingHandbook\BuildingHandbook;
use App\Models\Building\Building;
use App\Traits\ActionByUser;

class ServicePartners extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'bdc_service_partners';

    protected $fillable = ['customer','phone','email','pub_users_id', 'bdc_business_partners_id','timeorder','description', 'status','bdc_building_id','confirm_date','approved_id','bdc_handbook_id'];

    public function PubUsers()
    {
        return $this->belongsTo(Users::class, 'pub_users_id');
    }
    public function Approved()
    {
        return $this->belongsTo(Users::class, 'approved_id');
    }
    public function businesspartners()
    {
        return $this->belongsTo(BusinessPartners::class, 'bdc_business_partners_id');
    }
    public function building()
    {
        return $this->belongsTo(Building::class, 'bdc_building_id');
    }
     public function building_handbooks()
    {
        return $this->belongsTo(BuildingHandbook::class, 'bdc_handbook_id');
    }
     public function scopeFilter($query, $input)
    {
        foreach ($this->fillable as $value) {
            if (isset($input[$value])) {
                $query->where($value, $input[$value]);
            }
        }

        if (isset($input['service_partners_keyword'])) {
            $search = $input['service_partners_keyword'];
            $query->where(function ($q) use ($search) {
                foreach ($this->fillable as $value) {
                    $q->orWhere($value, 'LIKE', '%' . $search . '%');
                }
            });
        }
        return $query;
    }
}