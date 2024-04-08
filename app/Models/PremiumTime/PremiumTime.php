<?php

namespace App\Models\PremiumTime;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class PremiumTime extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'premium_time';

    protected $guarded = [];
    
}
