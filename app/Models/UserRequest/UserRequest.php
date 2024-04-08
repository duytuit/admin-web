<?php

namespace App\Models\UserRequest;

use App\Models\Apartments\Apartments;
use App\Models\PublicUser\V2\User;
use App\Models\PublicUser\V2\UserInfo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class UserRequest extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'user_requests';

    protected $guarded = [];

    public function apartment()
    {
        return $this->belongsTo(Apartments::class, 'apartment_id','id');
    }

    public function user_created_by()
    {
        return $this->belongsTo(UserInfo::class, 'user_id','user_id');
    }
    public function user_updated_by()
    {
        return $this->belongsTo(User::class, 'updated_by','id');
    }
    public function user_confirm_by()
    {
        return $this->belongsTo(User::class, 'user_confirm','id');
    }

}
