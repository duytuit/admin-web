<?php

namespace App\Models\Permissions;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ActionByUser;
use Illuminate\Support\Facades\Cache;

class Permission extends Model
{
    const SHOW_LEFT_MENU = 1;
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    use ActionByUser;
    protected $table = 'pub_permissions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'route_name', 'title', 'has_menu', 'module_id', 'status' , 'icon_web', 'type', 'position'
    ];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        // 'user_id',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }

    public static function getDetailPermissionById($id)
    {
        $permission = Cache::store('redis')->get(env('REDIS_PREFIX') . 'getDetailPermissionById_'.$id);
 
        if($permission){
             return $permission;
        }

        $permission = self::find($id);
        
        Cache::store('redis')->put(env('REDIS_PREFIX') . 'getDetailPermissionById_' . $id, $permission,24*60*60);

        return $permission;
    }

}