<?php

namespace App\Filter;

/**
 * Class Reply
 * @package App\Classes
 */
class SubTaskTemplateFilter
{
    public static function index($model, $request)
    {
        $model = collect($model);
        if(isset($request->department_id) && $request->department_id != null) {
            $model = $model->filter(function ($item) use ($request) {
                return $item->bdc_department_id == $request->department_id;
            })->values();
        }
        if(isset($request->title) && $request->title != null) {
            $model = $model->filter(function ($item) use ($request) {
                return $item->title == $request->title;
            })->values();
        }
        return $model;
    }
}
