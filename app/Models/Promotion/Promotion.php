<?php

namespace App\Models\Promotion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;
use Illuminate\Support\Facades\Cache;

class Promotion extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'promotion';

    protected $guarded = [];
}
