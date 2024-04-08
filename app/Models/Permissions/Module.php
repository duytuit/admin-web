<?php

namespace App\Models\Permissions;

use Illuminate\Database\Eloquent\Model;
use App\Models\Menu\MenuOfStaff;
use App\Traits\ActionByUser;

class Module extends Model
{

    use ActionByUser;
    protected $table = 'pub_module';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'icon_web','type'
    ];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        // 'user_id',
    ];

    /**
     * Relationships
     */
     /**
     * Relationships
     */
    public function menus()
    {
        return $this->hasMany(\App\Models\Permissions\Permission::class, 'module_id', 'id')->where('has_menu', \App\Models\Permissions\Permission::SHOW_LEFT_MENU )->orderBy('position');
    }

    public function permissions()
    {
        return $this->hasMany(\App\Models\Permissions\Permission::class, 'module_id')->orderBy('position');
    }
}