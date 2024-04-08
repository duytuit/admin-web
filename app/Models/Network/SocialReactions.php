<?php

namespace App\Models\Network;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class SocialReactions extends Model
{
    //


    use ActionByUser;
    protected $table = 'social_reactions';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'post_id', 'user_id', 'emotion'
    ];

    protected $hidden = [];

    protected $dates = ['deleted_at'];
}
