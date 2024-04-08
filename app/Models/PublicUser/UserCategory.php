<?php

namespace App\Models\PublicUser;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ActionByUser;

class UserCategory extends Model
{

    use ActionByUser;
    protected $table = 'pub_groups_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'pub_user_id', 'name', 'description', 'permissions_id', 'admin_id', 'created_by', 'created_at','app_id'
    ];
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

    /**
     * Get the phone record associated with the user.
     */

}