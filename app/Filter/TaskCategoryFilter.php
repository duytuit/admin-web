<?php

namespace App\Filter;

/**
 * Class Reply
 * @package App\Classes
 */
class TaskCategoryFilter
{
    public static function index($model, $request)
    {
        $model = collect($model);
        if(isset($request->category_name) && $request->category_name != null) {
            $model = $model->filter(function ($item) use ($request) {
                return $item->category_name == $request->category_name;
            })->values();
        }
        return $model;
    }
}
