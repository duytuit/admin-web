<?php

namespace Modules\Tasks\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ActionByUser;

class RoleType extends Model
{
    use ActionByUser;
    protected $table = 'v3_role_types';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [

    ];

    const TYPE_MANAGER = "ban_quan_ly";
    const TYPE_HEAD = "truong_bo_phan";
    const TYPE_EMPLOYEE = "nhan_vien";
}
