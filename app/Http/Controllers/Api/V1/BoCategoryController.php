<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\BoCategory;
use Illuminate\Http\Request;

class BoCategoryController extends Controller
{
    /**
     * Lấy danh sách dự án cho selec2
     *
     * @param Request $request->search
     * @return void
     */
    public function get_all_project(Request $request)
    {
        $where   = [['status', '=', 1]];
        $keyword = $request->input('search', '');
        if ($keyword) {
            $where[] = ['cb_title', 'like', '%' . $keyword . '%'];
        }
        $categories = BoCategory::where($where)->paginate(20);

        return response()->json($categories);
    }
}
