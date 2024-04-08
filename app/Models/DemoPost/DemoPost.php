<?php

namespace App\Models\DemoPost;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ActionByUser;

class DemoPost extends Model
{
    use ActionByUser;
    protected $table = 'demo_post';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'description'
    ];

    protected $hidden = ['pivot'];
}
