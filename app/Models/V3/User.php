<?php

namespace App\Models\V3;

use App\Models\Building\Building;
use App\Repositories\V3\UserRepository\UserRepository;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * Class User
 * @package App\Models
 * @property int $uuid
 * @property string $name
 * @property string $display_name
 * @property string $email
 */
use App\Traits\ActionByUser;
class User extends Authenticatable implements JWTSubject
{

    use HasRoles;
    use SoftDeletes;

    /**
     * @inheritdoc
     * @var string
     */
    use ActionByUser;
    protected $table = 'v3_users';

    protected $guard = "web";

    protected $primaryKey = "uuid";

    protected $keyType = "string";

    protected $guarded = [
        'id'
    ];

    public function getJWTIdentifier()
    {
        // TODO: Implement getJWTIdentifier() method.
    }

    public function getJWTCustomClaims()
    {
        // TODO: Implement getJWTCustomClaims() method.
    }

    /**
     * @return UserRepository
     */
    public function getRepositoryInstance(){
        return app(UserRepository::class);
    }

    public function getUserInfoId()
    {
        return Auth::user();
    }

    public function buildings()
    {
        return $this->belongsToMany(Building::class,
            "v3_user_has_building",
            "user_id",
            "building_id"
        );
    }
}