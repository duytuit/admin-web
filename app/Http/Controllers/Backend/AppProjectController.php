<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Controller;
use App\Models\AppProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Validator;

class AppProjectController extends Controller
{
    public function __construct()
    {
        $this->model = new AppProject();
    }

    public function attributes()
    {
        return [
            'title' => 'Tên App project',
        ];
    }

    /**
     * Danh sách bản ghi
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
        $data['meta_title'] = "QL app project";

        // Phân trang
        $data['per_page'] = Cookie::get('per_page', 20);

        $data['keyword'] = $request->input('keyword', '');

        $where = [];

        if ($data['keyword']) {
            $where[] = ['name', 'like', '%' . $data['keyword'] . '%'];
        }

        $app_projects = AppProject::searchBy([
            'where'    => $where,
            'per_page' => $data['per_page'],
        ]);

        $data['app_projects'] = $app_projects;

        return view('backend.app-projects.index', $data);
    }

    public function edit(Request $request)
    {
        $this->authorize('view', app(AppProject::class));
        $id = $request->id ? (int) $request->id : 0;

        $app_project = AppProject::findOrNew($id);

        $data['id']          = $id;
        $data['app_project'] = $app_project;

        $data['heading']    = 'Nhóm quyền';
        $data['meta_title'] = "QL Nhóm quyền";

        return view('backend.app-projects.edit_add', $data);
    }

    public function save(Request $request)
    {
        //phân quyền chỗ này
        $this->authorize('update', app(AppProject::class));
        //end check quyền

        $rules = [
            'name' => 'required',
            'code' => 'required',
        ];

        $id          = $request->input('id', 0);
        $app_project = AppProject::findOrNew($id);

        $validator = Validator::make($request->all(), $rules, [], $this->attributes());
        $errors    = $validator->messages();

        $name = AppProject::where('name', $request->name)->where('id', '!=', $id)->first();
        if ($name) {
            $errors->add('name', "Tên app project đã tồn tại");
        }
        $code = AppProject::where('code', $request->name)->where('id', '!=', $id)->first();
        if ($code) {
            $errors->add('code', "Mã app project đã tồn tại");
        }

        if ($errors->toArray()) {
            return back()->with(['errors' => $errors])->withInput();
        }

        if (!$request->has('_validate')) {

            $data = $request->only('name', 'code', 'admin_url', 'description');

            $update = $app_project->fill($data)->save();

            if ($update) {
                return redirect()->route('admin.app_projects.index')->with('success', 'Thêm menu mới thành công.');
            } else {
                return redirect()->route('admin.app_projects.index')->with('error', 'Thêm menu mới thất bại.');
            }
        }
    }

    public function ajax_get_app(Request $request)
    {
        $keyword = $request->input('search', '');
        if ($keyword) {
            $wheres[] = ['name', 'like', '%' . $keyword . '%'];
        }
        if (!empty($wheres)) {
            $apps = AppProject::where($wheres)->paginate(20);
        } else {
            $apps = AppProject::paginate(20);
        }

        return response()->json($apps);
    }

}
