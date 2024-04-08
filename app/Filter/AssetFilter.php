<?php

namespace App\Filter;

/**
 * Class Reply
 * @package App\Classes
 */
class AssetFilter
{
    public static function index($model, $request)
    {
        $model = collect($model);
        if(isset($request->asset_category_id) && $request->asset_category_id != null) {
            $model = $model->filter(function ($item) use ($request) {
                return $item->asset_category_id == $request->asset_category_id;
            })->values();
        }
        if(isset($request->area_id) && $request->area_id != null) {
            $model = $model->filter(function ($item) use ($request) {
                return $item->area_id == $request->area_id;
            })->values();
        }
        if(isset($request->department_id) && $request->department_id != null) {
            $model = $model->filter(function ($item) use ($request) {
                return $item->department_id == $request->department_id;
            })->values();
        }
        if(isset($request->name) && $request->name != null) {
            $model = $model->filter(function ($item) use ($request) {
                return stristr(strtolower($item->name), strtolower($request->name));
            })->values();
        }
        return $model;
    }
}
