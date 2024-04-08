<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Controller;
use App\Models\GroupMenu;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Validator;

class GroupMenuController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->model = new GroupMenu();
    }

    public function attributes()
    {
        return [
            'title'  => 'Tên nhóm menu',
            'app_id' => 'App project',
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
        $data['meta_title'] = "QL nhóm menu";

        // Phân trang
        $data['per_page'] = Cookie::get('per_page', 20);

        $data['keyword'] = $request->input('keyword', '');

        $where = [];

        if ($data['keyword']) {
            $where[] = ['title', 'like', '%' . $data['keyword'] . '%'];
        }

        $group_menus = GroupMenu::searchBy([
            'where'    => $where,
            'per_page' => $data['per_page'],
        ]);

        $data['group_menus'] = $group_menus;

        return view('backend.group-menus.index', $data);
    }

    public function edit(Request $request)
    {
        $this->authorize('view', app(GroupMenu::class));

        $id = $request->id ? (int) $request->id : 0;

        $group_menu  = GroupMenu::findOrNew($id);
        $parent_menu = Menu::whereNUll('parent_id')->orderBy('order')->get();
        $item_menu   = Menu::whereNotNUll('parent_id')->orderBy('order')->get()->groupBy('parent_id');

        $data['id']          = $id;
        $data['parent_menu'] = $parent_menu;
        $data['item_menu']   = $item_menu;
        $data['group_menu']  = $group_menu;

        $data['heading']    = 'Nhóm quyền';
        $data['meta_title'] = "QL Nhóm quyền";

        return view('backend.group-menus.edit_add', $data);
    }

    public function save(Request $request)
    {
        //phân quyền chỗ này
        $this->authorize('update', app(GroupMenu::class));
        //end check quyền

        $rules = [
            'title'  => 'required',
            'app_id' => 'required',
        ];

        $id   = $request->input('id', 0);
        $menu = GroupMenu::findOrNew($id);

        $validator = Validator::make($request->all(), $rules, [], $this->attributes());
        $errors    = $validator->messages();

        $check = GroupMenu::where('title', $request->title)->where('app_id', $request->app_id)->where('id', '!=', $id)->first();
        if ($check) {
            $errors->add('title', 'Tên nhóm menu đã tồn tại');
        }

        if ($errors->toArray()) {
            return back()->with(['errors' => $errors])->withInput();
        }

        if (!$request->has('_validate')) {

            $data = $request->only('title', 'app_id', 'menu_ids');

            $update = $menu->fill($data)->save();

            if ($update) {
                return redirect()->route('admin.group_menus.index')->with('success', 'Thêm menu mới thành công.');
            } else {
                return redirect()->route('admin.group_menus.index')->with('error', 'Thêm menu mới thất bại.');
            }
        }
    }
}
