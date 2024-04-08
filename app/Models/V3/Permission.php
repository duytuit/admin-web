<?php

namespace App\Models\V3;

use App\Repositories\V3\PermissionRepository\PermissionRepository;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Permission as BasePermission;

class Permission extends BasePermission {

    use SoftDeletes;

    const CHANGE_BUILDING_PERMISSION = "admin.building.changeBuilding";

    const ADMIN_MENU_APP = "admin.menu.app";

    /**
     * @return PermissionRepository
     */
    public function getRepositoryInstance(): PermissionRepository
    {
        return app(PermissionRepository::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(Permission::class,'parent_id','id');
    }

}