<?php

namespace App\Models\Task;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CheckList extends Model
{
    use SoftDeletes;
    protected $table = 'bdc_v2_task_form_checklist';
    protected $guarded  = [];
}
