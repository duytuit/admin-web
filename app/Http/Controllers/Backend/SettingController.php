<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Validator;

class SettingController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {

    }

    /**
     * Undocumented function
     * Mô tả các lỗi validate
     */
    public function messages()
    {
        return [
            'required' => ':attribute không được để trống',
        ];
    }

    /**
     * Undocumented function
     * Mô tả các field validate
     */
    public function attributes()
    {
        return [
            'config_key' => 'Config key',
        ];
    }

    public function index(Request $request)
    {

        $data['meta_title'] = 'Cấu hình';
        $data['settings']   = Setting::all();

        return view('backend.settings.index', $data);
    }

    public function edit($id)
    {
        $this->authorize('update', app(Setting::class));

        $data['meta_title'] = "Cấu hình";

        if ($id > 0) {
            $data['setting'] = SettingfindOrFail($id);
        } else {
            $data['setting'] = new Setting;
        }

        return view('backend.settings.edit_add', $data);
    }

    public function save_index(Request $request)
    {
        $this->authorize('edit', app(Setting::class));
        $datas = $request->data;

        foreach ($datas as $id => $setting) {
            $settings               = [];
            $settings['id']         = $id;
            $settings['config_key'] = $setting['config_key'];

            foreach ($setting['config_value'] as $value) {
                $settings['config_value'][$value['cf_key']] = $value['cf_value'];
            }

            $this->save_setting($settings);
        }

        return redirect(url('/admin/settings'))->with('success', 'Thành công!');
    }

    public function save(Request $request)
    {
        $this->authorize('update', app(Setting::class));

        $rules = [
            'config_key' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages(), $this->attributes());
        $errors    = $validator->messages();

        if ($errors->toArray()) {
            return back()->with(['errors' => $errors])->withInput();
        }

        if (!$request->has('_validate')) {
            $settings['config_key'] = $request->config_key;

            foreach ($request->config_value as $value) {
                $settings['config_value'][$value['cf_key']] = $value['cf_value'];
            }

            $this->save_setting($settings);

            return redirect(url('/admin/settings'))->with('success', 'Thành công!');
        }
    }

    public function save_setting($optioin)
    {
        $params = [
            'config_key'   => $optioin['config_key'],
            'config_value' => $optioin['config_value'],
        ];

        if (!empty($optioin['id'])) {
            $config = Setting::find($optioin['id']);
        }

        if (isset($config)) {
            $config->update($params);
        } else {
            $config = new Setting;
            $config->fill($params);
            $config->save();
        }
    }

}
