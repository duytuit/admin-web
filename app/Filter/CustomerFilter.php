<?php

namespace App\Filter;

use Carbon\Carbon;

/**
 * Class Reply
 * @package App\Classes
 */
class CustomerFilter
{
    public static function index($model, $request, $roleType = null)
    {
        $model = collect($model);

        if(isset($request->bdc_apartment_id) && $request->bdc_apartment_id != null) {
            $model = $model->filter(function ($item) use ($request) {
                return $item->bdc_apartment_id == $request->bdc_apartment_id;
            })->values();
        }
        if(isset($request->pub_user_profile_id) && $request->pub_user_profile_id != null) {
            $model = $model->filter(function ($item) use ($request) {
                return $item->pub_user_profile_id == $request->pub_user_profile_id;
            })->values();
        }
        if(isset($request->status_confirm) && $request->status_confirm != null) {
            $model = $model->filter(function ($item) use ($request) {
                return $item->status_confirm == $request->status_confirm;
            })->values();
        }
        if(isset($request->status_success_handover) && $request->status_success_handover != null) {
            $model = $model->filter(function ($item) use ($request) {
                return stristr($item->status_success_handover, $request->status_success_handover);
            })->values();
        }
        if(isset($request->start_date) && $request->start_date != null) {
            $model = $model->filter(function ($item) use ($request) {
                return Carbon::parse($item->handover_date) >= Carbon::parse($request->start_date);
            })->values();
        }
        if(isset($request->end_date) && $request->end_date != null) {
            $model = $model->filter(function ($item) use ($request) {
                return Carbon::parse($item->handover_date) <= Carbon::parse($request->end_date);
            })->values();
        }
        return $model;
    }
}
