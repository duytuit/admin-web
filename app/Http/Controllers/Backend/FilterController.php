<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FilterController extends Controller
{
    public function __construct()
    {
        $this->model = new Campaign();
    }

    public function index(Request $request)
    {
        $data['meta_title'] = "QL Chiến dịch";
        $this->authorize('index', app(Campaign::class));

        $searches['source'] = Setting::config_get('customer-source');

        // End setting tìm kiếm

        $data['per_page'] = Cookie::get('per_page', 20);
        $data['searches'] = $searches;

        //Tìm kiếm
        $where = [];
        if (!empty($request->title)) {
            $where[] = ['title', 'Like', "%{$request->title}%"];
        }

        if (!empty($request->project_id)) {
            $where[] = ['project_id', '=', $request->project_id];
        }

        if (!empty($request->source)) {
            $where[] = ['source', '=', $request->source];
        }

        if (!empty($request->begin_date) && empty($request->end_date)) {
            $where[] = ['updated_at', '>=', date('Y-m-d H:i:s', strtotime($request->begin_date))];
        } elseif (empty($request->begin_date) && !empty($request->end_date)) {
            $where[] = ['updated_at', '<=', date_add('Y-m-d H:i:s', strtotime($request->end_date))];
        } elseif (!empty($request->begin_date) && !empty($request->end_date)) {
            $where[] = ['updated_at', '>=', date('Y-m-d H:i:s', strtotime($request->begin_date))];
            $where[] = ['updated_at', '<=', date('Y-m-d H:i:s', strtotime($request->end_date))];
        }

        if ($where) {
            $campaigns = Campaign::searchBy(['where' => $where, 'per_page' => $data['per_page']]);
        } else {
            $campaigns = Campaign::searchBy(['per_page' => $data['per_page']]);
        }

        $data['campaigns'] = $campaigns;

        //End tìm kiếm
        $data_search = [
            'title'      => '',
            'project'    => [],
            'source'     => '',
            'begin_date' => '',
            'end_date'   => '',
        ];

        if (!empty($request->title)) {
            $data_search['title'] = $request->title;
        }

        if (!empty($request->project_id)) {
            $data_search['project']['id']   = $request->project_id;
            $data_search['project']['name'] = BoCategory::where('cb_id', $request->project_id)->first()->cb_title;
        }

        if (!empty($request->source)) {
            $data_search['source'] = $request->source;
        }

        if (!empty($request->begin_date)) {
            $data_search['begin_date'] = $request->begin_date;
        }

        if (!empty($request->end_date)) {
            $data_search['end_date'] = $request->end_date;
        }

        $data['data_search'] = $data_search;

        return view('backend.campaigns.index', $data);
    }

    public function edit($id = 0)
    {
        $data['meta_title'] = "QL Chiến dịch";
        $this->authorize('view', app(Campaign::class));

        $campaign                = Campaign::findOrNew($id);
        $customers               = CampaignAssign::searchBy(['where' => ['campaign_id' => $id]]);
        $data['customer_source'] = Setting::config_get('customer-source');
        $data['campaign']        = $campaign;
        $data['customers']       = $customers;
        $data['id']              = $id;

        return view('backend.campaigns.edit_add', $data);
    }

    public function save(Request $request, $id = 0)
    {
        //phân quyền chỗ này
        $this->authorize('update', app(Campaign::class));

        //end check quyền
        $rules = [
            'title'      => 'required',
            'project_id' => 'required',
        ];

        if (!$id) {
            $rules[] = [
                'title' => 'unique:campaigns',
            ];
        }

        $validator = Validator::make($request->all(), $rules, $this->messages(), $this->attributes());
        $errors    = $validator->messages();

        if ($errors->toArray()) {
            return back()->with(['errors' => $errors])->withInput();
        }

        if (!$request->has('_validate')) {

            $file        = $request->file('file_user_cus');
            $data_import = [];

            if ($file) {
                $data_import = $this->getDataFile($file);
            }

            // edit hoặc thêm mới mà ko import file
            if (!$data_import) {
                $check = $this->saveDataImport($request, $id);
                return redirect(url('/admin/campaigns'))->with('success', 'Cập nhật thành công!');
            }

            if ($data_import['data']) {
                $check = $this->saveDataImport($request, $id, $data_import['data']);

                // Nếu có lỗi do hệ thống
                if (!$check) {
                    return back()->with('error', 'Cập nhật không thành công do phát sinh lỗi!');
                } else {
                    // Nếu danh sách import có khách hàng hoặc nhân viên bị trùng
                    if ($data_import['data']['duplicate']) {
                        $duplicate = $data_import['data']['duplicate'];

                        if ($duplicate['customer'] || $duplicate['staff']) {
                            $duplicate = [
                                'success' => 'Thêm chiến dich thành công!',
                                'data'    => $duplicate,
                            ];

                            return redirect()->route('admin.campaigns.edit', ['id' => $check->id])->with('duplicate', $duplicate);

                        } else {
                            return redirect()->route('admin.campaigns.index')->with('success', 'Cập nhật chiến dịch thành công');
                        }
                    } else {
                        // Hoàn hảo ko có gì hết :)
                        return redirect()->route('admin.campaigns.index')->with('success', 'Cập nhật chiến dịch thành công');
                    }
                }
            } else {
                // Khi có thông tin TK nhân viên không chính xác
                return back()->with('errors_user', $data_import['messages'])->withInput();
            }
        }
    }

    // public function updateNumber()
    // {
    //     # code...
    // }

}
