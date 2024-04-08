<?php

namespace App\Http\Controllers\Apartments;

use App\Commons\ApiResponse;
use App\Http\Controllers\BuildingController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApartmentGroupController extends BuildingController
{


    public function store(Request $request)
    {

        $name = $request->get('name');
        $description = $request->get('description');
        $list_apartment = $request->get('list_apartment');

        $list_apartment = \GuzzleHttp\json_decode($list_apartment);

        $ap_group_id = DB::table('bdc_apartment_groups')
            ->insertGetId([
                'name'=>$name,
                'description'=>$description,
                'bdc_building_id'=>$this->building_active_id,
                'status'=>1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

        if ($ap_group_id) {
            DB::table('bdc_apartments')
                ->whereIn('id', $list_apartment)
                ->update([
                    'bdc_apartment_group_id' => $ap_group_id
                ]);

        }

        return ApiResponse::responseSuccess([
            'msg'=>"Thêm loại căn hộ thành công"
        ]);
    }

    public function update(Request $request)
    {
        $id = $request->get('id');
        $name = $request->get('name');
        $description = $request->get('description');
        $list_apartment = $request->get('list_apartment');

        $list_apartment = \GuzzleHttp\json_decode($list_apartment);

        $group = DB::table('bdc_apartment_groups')->find($id);

        if ($group) {
            DB::table('bdc_apartments')
                ->where('bdc_apartment_group_id', $id)
                ->update([
                    'bdc_apartment_group_id' => null
                ]);

            DB::table('bdc_apartment_groups')
                ->where('id',$id)
                ->update([
                    'name'=>$name,
                    'description'=>$description,
                    'bdc_building_id'=>$this->building_active_id,
                    'status'=>1,
                    'updated_at' => Carbon::now(),
                ]);

            DB::table('bdc_apartments')
                ->whereIn('id', $list_apartment)
                ->update([
                    'bdc_apartment_group_id' => $id
                ]);

        }

        return ApiResponse::responseSuccess([
            'msg'=>"Cập nhật loại căn hộ thành công"
        ]);

    }

    public function delete(Request $request)
    {
        $ids = $request->get('ids');

        $ids = \GuzzleHttp\json_decode($ids);
        $response = null;

        DB::table('bdc_apartment_groups')->delete($ids);

        DB::table('bdc_apartments')
            ->whereIn('bdc_apartment_group_id',$ids)
            ->update([
                'bdc_apartment_group_id' => null
            ]);

        return ApiResponse::responseSuccess([]);
    }

    public function addApartment(Request $request)
    {
        $id = $request->get('id');
        $apartmentIds = $request->get('apartmentIds');
        $apartmentIds = \GuzzleHttp\json_decode($apartmentIds);

        $group = DB::table('bdc_apartment_groups')->find($id);

        if ($group) {
            DB::table('bdc_apartments')
                ->whereIn('id',$apartmentIds)
                ->update([
                    'bdc_apartment_group_id' => $group->id
                ]);
            return ApiResponse::responseSuccess([]);
        }
        else {
            return ApiResponse::responseError([]);
        }

    }

}
