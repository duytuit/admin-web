<?php

namespace App\Models\PublicUser\V2;

use App\Models\Apartments\V2\UserApartments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class UserInfo extends Model
{
    use SoftDeletes;

    use ActionByUser;
    protected $table = 'bdc_v2_user_info';

    protected $guarded = [];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function apartment()
    {
        return $this->belongsTo(UserApartments::class, 'apartment_id', 'id');
    }
    public function user_token()
    {
        return $this->belongsTo(TokenUser::class, 'user_id', 'user_id');
    }
    public function building()
    {
        return $this->hasOne(UserApartments::class, 'user_info_id', 'id');
    }
}
