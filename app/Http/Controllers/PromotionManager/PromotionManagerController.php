<?php

namespace App\Http\Controllers\PromotionManager;

use App\Commons\Api;
use App\Http\Controllers\BuildingController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DateTime;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PromotionManagerController extends BuildingController
{
    public function index(Request $request)
    {
        $data['meta_title'] = 'Quản lý khuyến mãi';
        $data['filter'] = $request->all();
        $request->request->add(['building_id' => $this->building_active_id]);
        // if(isset($data['filter']['asset_category_id'])){
        //     $data['_asset_category'] = AssetCategory::find($data['filter']['asset_category_id']);
        // }
        // if(isset($data['filter']['office_id'])){
        //     $data['_office_asset'] = AssetArea::find($data['filter']['office_id']);
        // }
        // if(isset($data['filter']['department_id'])){
        //     $data['_department_asset'] = Department::find($data['filter']['department_id']);
        // }
        $array_search='';
        $i=0;
        foreach ($request->all() as $key => $value) {
            if($i == 0){
                $param='?'.$key.'='.(string)$value;
            }else{
                $param='&'.$key.'='.(string)$value;
            }
            $i++;
            $array_search.=$param;
        }

        $data['array_search'] = $array_search;
        $data['building_id'] = $this->building_active_id;
        return view('promotion_manager.index', $data);
    }
    public function store(Request $request)
    {
        $data = $request->all();
        $begin = new DateTime($data['begin']);
        $end = new DateTime($data['end']);
        $interval = date_diff($begin, $end);
        $data['condition'] = $interval->format('%m');

        $result = Api::POST('admin/promotion/addPromotion?building_id=73', $data);

        if ($result->status == true) {
            $message = [
                'error'  => 0,
                'status' => 'success',
                'mess'    => $result->mess,
            ];
            return response()->json($message);
        } else {
            $message = [
                'error'  => -1,
                'status' => 'error',
                'mess'    => $result->mess,
            ];
            return response()->json($message);
        }
    }
    public function edit(Request $request)
    {
        $id = $request->id;
        $data = [];
        return view('promotion_manager.modals.add-promotion-manager', compact('data'));
    }
    public function update(Request $request)
    {
        $data = $request->all();
        $begin = new DateTime($data['begin']);
        $end = new DateTime($data['end']);
        $interval = date_diff($begin, $end);
        $data['condition'] = $interval->format('%m');
        $result = Api::POST('admin/promotion/addPromotion?building_id=73', $data);
        if ($result->status == true) {
            $message = [
                'error'  => 0,
                'status' => 'success',
                'mess'    => $result->mess,
            ];
            return response()->json($message);
        } else {
            $message = [
                'error'  => -1,
                'status' => 'error',
                'mess'    => $result->mess,
            ];
            return response()->json($message);
        }
    }
    public function change_status(Request $request)
    {
        $ListPromotion = Api::GET('admin/promotion/getListPromotion', ['building_id' => 73, 'type' => 'service_vehicle'])->data;
        $promotion = '';
        $id = $request->id;

        // echo $id;
        foreach ($ListPromotion->list as $item) {
            if ($id == $item->id) {
                $promotion = $item;
            }
        }

        $data = [
            'name' => $promotion->name,
            'discount' => $promotion->discount,
            'type_discount' => $promotion->type_discount,
            'type' => $promotion->type,
            'begin' => $promotion->begin,
            'end' => $promotion->end,
            'service_id' => $promotion->service_id,
            'condition' => $promotion->condition,
            'service_type' =>  $promotion->service_type,
            'id' => $promotion->id,
        ];

        if ($promotion->status == 0) {
            $data['status'] = 1;
        } else {
            $data['status'] = 0;
        }
        $result = Api::POST('admin/promotion/addPromotion?building_id=73', $data);
        if ($result->status == true) {
            $message = [
                'error'  => 0,
                'status' => 'success',
                'mess'    => $result->mess,
            ];
            return response()->json($message);
        } else {
            $message = [
                'error'  => -1,
                'status' => 'error',
                'mess'    => $result->mess,
            ];
            return response()->json($message);
        }
    }
}
