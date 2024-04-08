<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;
use Validator;

class MenuController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->model = new Menu();
    }

    public function attributes()
    {
        return [
            'title' => 'Tên menu',
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
        $data['meta_title'] = "QL Menu";

        $parent_menu         = Menu::whereNUll('parent_id')->orderBy('order')->get();
        $item_menu           = Menu::whereNotNUll('parent_id')->orderBy('order')->get()->groupBy('parent_id');
        $data['parent_menu'] = $parent_menu;
        $data['item_menu']   = $item_menu;

        return view('backend.menus.index', $data);
    }

    /**
     * Sửa bản ghi
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function show(Request $request)
    {
        $id = $request->id ? (int) $request->id : 0;

        $menu = Menu::find($id);

        if ($menu) {
            return response()->json(['data' => $menu]);
        } else {
            return response()->json(['errors' => "Menu không tồn tại"]);
        }

    }

    /**
     * Lưu bản ghi
     *
     * @param  CategoryRequest  $request
     * @param  int  $id
     * @return Response
     */
    public function updateValidator(Request $request)
    {
        //end check quyền
        $rules = [
            'title' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules, [], $this->attributes());
        $errors    = $validator->messages();

        if ($errors->toArray()) {
            return response()->json(['errors' => $errors]);
        }
    }

    public function save(Request $request)
    {
        //phân quyền chỗ này
        $this->authorize('update', app(Menu::class));

        if (!$request->has('_validate')) {
            $id = $request->input('id', 0);

            $data  = $request->only('title', 'url', 'icon', 'parent_id');

            $menu = Menu::findOrNew($id);

            $update = $menu->fill($data)->save();

            if ($update) {
                return redirect()->route('admin.menus.index')->with('success', 'Thêm menu mới thành công.');
            } else {
                return redirect()->route('admin.menus.index')->with('error', 'Thêm menu mới thất bại.');
            }
        }
    }

    public function delete(Request $request)
    {
        $id = $request->id ? (int) $request->id : 0;

        $menu = Menu::find($id);

        $menu->delete();
    }

    public function order_item(Request $request)
    {
        $menuItemOrder = json_decode($request->input('order'));

        $this->orderMenu($menuItemOrder, null);
        return redirect()->route('admin.menus.index')->with('success', 'Cập nhật menu thành công.');
    }

    private function orderMenu(array $menuItems, $parentId)
    {
        foreach ($menuItems as $index => $menuItem) {
            $item            = Menu::findOrFail($menuItem->id);
            $item->order     = $index + 1;
            $item->parent_id = $parentId;
            $item->save();

            if (isset($menuItem->children)) {
                $this->orderMenu($menuItem->children, $item->id);
            }
        }
    }
}
