<?php

namespace App\Models\PublicUser\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class TokenUser extends Model
{
    use SoftDeletes;
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    use ActionByUser;
    protected $table = 'bdc_v2_token';
    protected $guarded = [];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'created_by',
        'updated_by',
        'updated_at',
        'created_at',
    ];

}
