<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\City;
use App\Models\District;
use Illuminate\Http\Request;
use App\Http\Resources\AddressResource;

class AddressController extends Controller
{
    /**
     * Lấy danh sách tỉnh thành phố
     *
     * @param
     * @return void
     */
    public function city(Request $request)
    {
        $where   = [];
        $keyword = $request->input('search', '');
        if ($keyword) {
            $where[] = ['name', 'like', '%' . $keyword . '%'];
        }
        $city = City::where($where)->get();

        return AddressResource::collection($city);
    }

    /**
     * Lấy danh sách quận huyện theo mã tỉnh thành phố
     *
     * @param
     * @return void
     */
    public function district(Request $request)
    {
        $where[] = ['city_code', '=', $request->city];
        $keyword = $request->input('search', '');
        if ($keyword) {
            $where[] = ['name', 'like', '%' . $keyword . '%'];
        }
        $district = District::where($where)->get();

        return AddressResource::collection($district);
    }

}
