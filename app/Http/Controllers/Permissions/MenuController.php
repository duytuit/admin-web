<?php

namespace App\Http\Controllers\Permissions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;
use App\Services\AppConfig;
use App\Repositories\Permissions\ModuleRepository;

class MenuController extends Controller
{

    public function __construct(ModuleRepository $model)
    {
        //$this->middleware('auth', ['except'=>[]]);
        //$this->middleware('route_permision');
        $this->model = $model;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // dd($this->model->paginate());
        // return view('system.menu.index')->with('data', $this->model->paginate())->with('group', $this->group_menu->paginate());
        return view('system.menu.index')->with('data', $this->model->paginate())->with('meta_title', 'Module');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['meta_title'] = 'Add new Module';
        $data['count'] = $this->model->countType();
        return view('system.menu.create',$data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'icon_web' => 'required'
        ]);

        $data = $request->only('name', 'description', 'icon_web','type');

        $this->model->create($data);

        return redirect()->route('admin.system.menu.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data['meta_title'] = 'Edit Module';
        $data['item'] = $this->model->findMenu($id);
        return view('system.menu.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'icon_web' => 'required'
        ]);
        $data = $request->only('name', 'description', 'icon_web','type');
        $this->model->update($data, $id);
        return redirect()->route('admin.system.menu.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $permission = $this->model->findMenu($id)->menus;
        if ($permission->count() == 0) {
            $this->model->findMenu($id)->delete();
            return response()->json([
                'success'=> true,
                'message' => 'Xóa thành công'
            ]);
        } else {
            return response()->json([
                'success'=> false,
                'message' => 'Không thể xóa module này! Nó bao gồm nhiều permisson. Xóa chúng trước khi xóa module'
            ]);
        }
    }
}