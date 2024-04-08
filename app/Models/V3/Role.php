<?php

namespace App\Models\V3;

use App\Repositories\V3\PermissionRepository\PermissionRepository;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role as BaseRole;

/**
 * Class Role
 * @package App\Models\V3
 * @property int $id
 * @property string $name
 * @property RoleType $roleType
 */
class Role extends BaseRole {

    use SoftDeletes;

    const ROLE_ADMIN = "admin";
    const ROLE_SUPER_ADMIN = "super-admin";

    /**
     * @inheritdoc
     * @var array
     */
    protected $appends = [
        'permission_names',
    ];

    /**
     * @return PermissionRepository
     */
    public function getRepositoryInstance(): PermissionRepository
    {
        return app(PermissionRepository::class);
    }

    public function getPermissionNamesAttribute()
    {
        return $this->getAllPermissions()->pluck('name')->toArray();
    }

    /**
     * @return HasOne
    */
    public function roleType(): HasOne
    {
        return $this->hasOne(RoleType::class, 'id','role_type_id');
    }

}