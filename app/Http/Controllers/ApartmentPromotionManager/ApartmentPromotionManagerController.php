<?php

namespace App\Http\Controllers\ApartmentPromotionManager;

use App\Commons\Api;
use App\Http\Controllers\BuildingController;
use App\Models\Apartments\Apartments;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ApartmentPromotionManagerController extends BuildingController
{
    public function index(Request $request)
    {
        $data['meta_title'] = 'Quản lý căn hộ được khuyến mãi';
        $data['filter'] = $request->all();
        $request->request->add(['building_id' => $this->building_active_id]);

         if(isset($data['filter']['apartment_id'])){
             $data['apartment'] = Apartments::find($data['filter']['apartment_id']);
         }

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
        return view('apartment-promotion-manager.index', $data);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $result = Api::POST('admin/promotion/addPromotionApartment?building_id=73', $data);
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

    public function update(Request $request)
    {
        $data = $request->all();
        $result = Api::POST('admin/promotion/updatePromotionApartment?building_id=73', $data);
        echo json_encode($result);
    }
    public function edit(Request $request)
    {
        $data = $request->all();
        return view('apartment-promotion-manager.modals.edit-apartment-promotion-manager', compact('data'));
    }
    public function delete(Request $request)
    {
        $id = $request->id;
        $result = Api::POST('admin/promotion/delPromotionApartment?building_id=73', ['id' => $id]);
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
