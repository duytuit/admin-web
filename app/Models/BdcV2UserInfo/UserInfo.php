<?php

namespace App\Models\BdcV2UserInfo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class UserInfo extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'bdc_v2_user_info';
    /*protected $fillable = [
    ];*/

    protected $guarded = [];
}
