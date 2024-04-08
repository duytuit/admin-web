<?php

namespace App\Models\V3;

use App\Models\Model;

/**
 * Class RoleType
 * @package App\Models\V3
 * @property int $id
 * @property string $name
 * @property string $display_name
 * @property string $description
 */
use App\Traits\ActionByUser;
class RoleType extends Model
{

    /**
     * @inheritdoc
     * @var string
     */
    use ActionByUser;
    protected $table = 'v3_role_types';

    const ROLE_ADMIN = "admin";
    const ROLE_SUPER_ADMIN = "super-admin";
    const ROLE_BAN_QUAN_LY = "ban_quan_ly";
    const ROLE_TRUONG_BO_PHAN = "truong_bo_phan";
    const ROLE_NHAN_VIEN = "nhan_vien";

    const ROLE_COMMON = [
        self::ROLE_BAN_QUAN_LY,
        self::ROLE_TRUONG_BO_PHAN,
        self::ROLE_NHAN_VIEN
    ];

    /**
     * @inheritdoc
     * @var int
     */
    protected $primaryKey = 'id';

}