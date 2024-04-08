<?php

namespace App\Filter;

use Carbon\Carbon;

/**
 * Class Reply
 * @package App\Classes
 */
class MaintenaceAssetFilter
{
    public static function index($model, $request = null)
    {
        $model = collect($model);
        if(isset($request->title) && $request->title != null) {
            $model = $model->filter(function ($item) use ($request) {
                return stristr($item->title, $request->title);
            })->values();
        }
        if(isset($request->department_id) && $request->department_id != null) {
            $model = $model->filter(function ($item) use ($request) {
                return $item->asset->department_id == $request->department_id;
            })->values();
        }
        if(isset($request->asset_id) && $request->asset_id != null) {
            $model = $model->filter(function ($item) use ($request) {
                return $item->asset_id == $request->asset_id;
            })->values();
        }
        if(isset($request->start_date) && $request->start_date != null) {
            $model = $model->filter(function ($item) use ($request) {
                return Carbon::parse($item->maintenance_time) >= Carbon::parse($request->start_date);
            })->values();
        }
        if(isset($request->end_date) && $request->end_date != null) {
            $model = $model->filter(function ($item) use ($request) {
                return Carbon::parse($item->maintenance_time) <= Carbon::parse($request->end_date);
            })->values();
        }

        return $model;
    }
}
