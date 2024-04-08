<?php

namespace App\Filter;

/**
 * Class Reply
 * @package App\Classes
 */
class WorkShiftFilter
{
    public static function tasks($model, $request)
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
        if(isset($request->task_name) && $request->task_name != null) {
            $model = $model->filter(function ($item) use ($request) {
                return stristr($item->task_name, $request->task_name);
            })->values();
        }
        return $model;
    }
}
