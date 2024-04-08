<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\UrlAlias;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CategoryController extends Controller
{

    /**
     * Construct
     */
    public function __construct()
    {
        $this->model    = new Category();
        $this->resource = new CategoryResource(null);

        Carbon::setLocale('vi');
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $type     = $request->input('type', 'article');
        $per_page = $request->input('per_page', 10);
        $per_page = $per_page > 0 ? $per_page : 10;

        $columns = $this->model->getTableColumns();

        $excludes = ['user_id', 'deleted_at'];

        foreach ($columns as $column) {
            if (!in_array($column, $excludes)) {
                $allowFields[] = $column;
            }
        }

        $where = [['status', 1]];

        if (in_array($type, ['article', 'event', 'voucher'])) {
            $where[] = ['type', '=', $type];
        }

        $select    = $this->_select($request, $allowFields);
        $condition = $this->_filters($request, $columns);
        $order_by  = $this->_sort($request, $columns);

        $items = $this->model->select($select)
            ->where($where)
            ->where($condition)
            ->orderByRaw($order_by)
            ->paginate($per_page);

        return $this->resource->many($items);
    }

    /**
     * Save a resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request)
    {
        // validate
        app(CategoryRequest::class);

        $user = $this->getApiUser();

        $id   = (int) $request->id;
        $type = $request->input('type', 'article');

        $columns = $this->model->getTableColumns();
        $input   = $request->only($columns);

        $input['type']   = $type;
        $input['status'] = $request->input('status', 0);

        $item = Category::findOrNew($id);

        $item->user_id = $user->id;
        $item->fill($input)->save();

        // url alias
        if (empty($item->alias)) {
            $slug = str_slug($item->title);
        } else {
            $slug = $request->alias;
        }

        // save alias
        if ($slug) {
            $uri = 'categories/' . $item->id;
            $url = UrlAlias::saveAlias($uri, $slug, '');

            $item->url_id = $url->id;
            $item->alias  = $url->alias;
            $item->save();
        }

        return $this->resource->one($item);
    }
}
