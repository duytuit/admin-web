<?php

namespace App\Models\BusinessPartners;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\BuildingHandbook\BuildingHandbook;
use App\Traits\ActionByUser;

class BusinessPartners extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'bdc_business_partners';

    protected $fillable = ['name', 'mobile', 'contact', 'email','representative', 'position', 'description', 'status','pub_users_id','bdc_building_id','address'];

    public function handbooks()
    {
        return $this->hasMany(BuildingHandbook::class, 'bdc_business_partners_id');
    }
     public function scopeFilter($query, $input)
    {
        foreach ($this->fillable as $value) {
            if (isset($input[$value])) {
                $query->where($value, $input[$value]);
            }
        }

        if (isset($input['business_partners_keyword'])) {
            $search = $input['business_partners_keyword'];
            $query->where(function ($q) use ($search) {
                foreach ($this->fillable as $value) {
                    $q->orWhere($value, 'LIKE', '%' . $search . '%');
                }
            });
        }
        return $query;
    }
}