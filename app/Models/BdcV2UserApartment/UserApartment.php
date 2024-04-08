<?php

namespace App\Models\BdcV2UserApartment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class UserApartment extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'bdc_v2_user_apartment';
    /*protected $fillable = [
    ];*/

    protected $guarded = [];
}
