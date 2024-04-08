<?php

namespace App\Models;

use App\Models\Model;
use App\Models\BoUser;
use App\Models\City;
use App\Models\District;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exchange extends Model
{
    use SoftDeletes;

    protected $guarded = [];
    protected $casts = [
        'location'  => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(BoUser::class, 'user_id', 'ub_id');
    }

    public function city_code()
    {
        return $this->belongsTo(City::class, 'city', 'code');
    }

    public function district_code()
    {
        return $this->belongsTo(District::class, 'district', 'code');
    }
}
