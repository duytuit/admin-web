<?php

namespace Modules\Assets\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Tasks\Entities\SubTask;
use Modules\Tasks\Entities\TaskUser;
use App\Traits\ActionByUser;

class Asset extends Model
{
    use ActionByUser;
    protected $table = 'bdc_assets';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'bdc_building_id',
        'department_id', // Bộ phận
        'asset_category_id', // Chuyên mục tài sản
        'area_id', // Khu vực
        'name', // Tên tài sản
        'quantity', // Số lượng
        'bdc_period_id', // Kỳ bảo trì
        'maintainance_date', // Ngày bắt đầu bảo trì
        'buying_date', // Ngày mua tài sản
        'price', // Giá mua
        'place', // Nơi đặt mua
        'buyer', // Người mua
        'follower', // Người theo dõi
        'asset_note', // Ghi chú
        'warranty_period', // Hạn bảo hành
        'images', // Hình ảnh
        'status' // trạng thái
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

    const STATUS = [
        'not_yet_started' => 'not_yet_started',
        'processing' => 'processing',
        'started' => 'started',
        'pending' => 'pending',
        'return' => 'return',
        'switch_request' => 'switch_request',
        'deny_request' => 'deny_request',
        'done' => 'done',
    ];

    const TYPE_PHATSINH = 'phat_sinh';
    const TYPE_LAPLAI = 'lap_lai';

    public function subTasks()
    {
        return $this->hasMany(SubTask::class, 'task_id');
    }

    public function taskUsers()
    {
        return $this->hasMany(TaskUser::class, 'task_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }
}
