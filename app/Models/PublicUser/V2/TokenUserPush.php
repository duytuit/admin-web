<?php

namespace App\Models\PublicUser\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class TokenUserPush extends Model
{
    use SoftDeletes;

    use ActionByUser;
    protected $table = 'bdc_v2_token_push';
    protected $guarded = [];
    

}
