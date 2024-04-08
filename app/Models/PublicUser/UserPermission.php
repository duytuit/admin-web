<?php

namespace App\Models\PublicUser;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ActionByUser;

class UserPermission extends Model
{
    use ActionByUser;
    protected $table = 'pub_user_permissions';

    protected $fillable = ['pub_user_id', 'permissions'];

    public $timestamps = false;
}
