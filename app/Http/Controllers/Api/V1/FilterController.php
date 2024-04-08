<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use App\Models\Filter;
use Illuminate\Http\Request;

class FilterController extends Controller
{
    public function __construct(Request $request)
    {
        $this->model = new Filter();
        $_jwt = $request->header('Content-MD5');
        if ($_jwt !== env('ContentMD5')) {
            $this->middleware('auth:api');
        }
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $columns = (new Filter)->getTableColumns();

        $unset = ['user_id'];

        $allowFields = [];
        foreach ($columns as $column) {
            if (!in_array($column, $unset)) {
                $allowFields[] = $column;
            }
        }

        $select    = $this->_select($request, $allowFields);
        $condition = $this->_filters($request, $columns);
        $order_by  = $this->_sort($request, $columns);

        $filters = Filter::select($select)
            ->where('status', 1)
            ->where($condition)
            ->orderByRaw($order_by)
            ->get()
            ->groupBy('key');

        $new_filters = [];

        foreach ($filters as $key => $filter) {
            $value = [];
            $title = "";
            foreach ($filter as $item) {
                $value[] = [
                    'id'    => $item->id,
                    'value' => $item->value,
                ];

                $title = $item->title;
            }
            $new_filters[] = [
                'key'   => $key,
                'title' => $title,
                'value' => $value,
            ];
        }
        $new_filters = collect($new_filters);

        $data = [
            'status' => true,
            'data'   => $new_filters,
            'code'   => 200,
        ];

        return response()->json($data);
    }

}
