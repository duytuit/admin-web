<?php

namespace App\Filter;

use Carbon\Carbon;

/**
 * Class Reply
 * @package App\Classes
 */
class TaskFilter
{
    public static function index($model, $request, $roleType = null)
    {
        $model = collect($model);
        if(isset($request->task_category_id) && $request->task_category_id != null) {
            $model = $model->filter(function ($item) use ($request) {
                return $item->task_category_id == $request->task_category_id;
            })->values();
        }
        if(isset($request->bdc_department_id) && $request->bdc_department_id != null) {
            $model = $model->filter(function ($item) use ($request) {
                return $item->bdc_department_id == $request->bdc_department_id;
            })->values();
        }
        if(isset($request->maintenance_asset_id) && $request->maintenance_asset_id != null) {
            $model = $model->filter(function ($item) use ($request) {
                return $item->maintenance_asset_id == $request->maintenance_asset_id;
            })->values();
        }
        if(isset($request->start_date) && $request->start_date != null) {
            $model = $model->filter(function ($item) use ($request) {
                // return stristr($item->start_date, $request->start_date);
                return Carbon::parse($item->start_date) >= Carbon::parse($request->start_date);
            })->values();
        }
        if(isset($request->end_date) && $request->end_date != null) {
            $model = $model->filter(function ($item) use ($request) {
                // return stristr($item->start_date, $request->start_date);
                return Carbon::parse($item->start_date) <= Carbon::parse($request->end_date);
            })->values();
        }
        if(isset($request->task_name) && $request->task_name != null) {
            $model = $model->filter(function ($item) use ($request) {
                return stristr($item->task_name, $request->task_name);
            })->values();
        }
        if(isset($request->status) && $request->status != null) {
            $model = $model->filter(function ($item) use ($request) {
                return $item->status == $request->status;
            })->values();
        }
        if(isset($request->type) && $request->type != null) {
            $model = $model->filter(function ($item) use ($request) {
                return $item->type == $request->type;
            })->values();
        }
        if(isset($request->user_id) && $request->user_id != null) {
            $model = $model->filter(function ($item) use ($request) {
                if($item->task_users != null) {
                    foreach($item->task_users as $taskUser) {
                        if($taskUser->user_id == $request->user_id || $item->supervisor == $request->user_id) {
                            return true;
                        }
                    }
                }
            })->values();
        }
        return $model;
    }
}
