<?php

namespace App\Models\Configs;

use App\Models\BdcApartmentDebit\ApartmentDebit;
use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Models\BdcBills\Bills;
use App\Models\BdcDebitDetail\DebitDetail;
use App\Models\Building\Building;
use App\Models\Customers\Customers;
use App\Models\SystemFiles\SystemFiles;
use App\Models\Vehicles\Vehicles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\PublicUser\Users;
use App\Models\PublicUser\UserInfo;
use App\Traits\ActionByUser;

class Configs extends Model
{
    use SoftDeletes;
    //
    use ActionByUser;
    protected $table = 'configs';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'value', 'key', 'app_id', 'note','bdc_building_id', 'created_by', 'status', 'publish','default'
    ];

    protected $hidden = [];

    protected $dates = ['deleted_at'];

    public function pub_profile()
    {
        return $this->belongsTo(UserInfo::class, 'created_by');
    }

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
                foreach ($this->fillable as $value) {
                    $q->orWhere($value, 'LIKE', '%' . $search . '%');
                }
            });
        }
        return $query;
    }

    
}
