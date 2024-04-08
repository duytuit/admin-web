<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Controller;
use App\Models\City;
use App\Models\District;
use Illuminate\Http\Request;

class CityController extends Controller
{

    /**
     * Constructor.
     */
    public function __construct()
    { }

    public function ajax_get_city(Request $request)
    {
        $keyword = $request->input('search', '');
        if ($keyword) {
            $wheres[] = ['name', 'like', '%' . $keyword . '%'];
        }
        if (!empty($wheres)) {
            $cities = City::where($wheres)->paginate(20);
        } else {
            $cities = City::paginate(20);
        }

        return response()->json($cities);
    }

    public function ajax_district(Request $request)
    {
        $wheres[] = ['city_code', '=', $request->city];
        $keyword  = $request->input('search', '');
        if ($keyword) {
            $wheres[] = ['name', 'like', '%' . $keyword . '%'];
        }
        $district = District::where($wheres)->paginate(20);

        return response()->json($district);
    }
}
