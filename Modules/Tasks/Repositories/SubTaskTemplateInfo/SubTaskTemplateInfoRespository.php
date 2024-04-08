<?php

namespace Modules\Tasks\Repositories\SubTaskTemplateInfo;

use App\Helpers\RedisHelper;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Modules\Tasks\Entities\SubTaskTemplateInfo;

class SubTaskTemplateInfoRespository extends BaseRepository implements SubTaskTemplateInfoInterface
{
    public function getModel()
    {
        return SubTaskTemplateInfo::class;
    }

    public function deleteBySubTaskTempateId($subTaskTemplateId)
    {
        return $this->model->where(['sub_task_template_id' => $subTaskTemplateId])->delete();
    }
}
