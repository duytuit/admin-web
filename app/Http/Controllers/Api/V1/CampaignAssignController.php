<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\CampaignAssignResource;
use App\Models\Campaign;
use App\Models\CampaignAssign;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Validator;

class CampaignAssignController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct(Request $request)
    {
        $this->model = new CampaignAssign();
        // $user = \Auth::user();

        $_jwt = $request->header('Content-MD5');
        if ($_jwt !== env('ContentMD5')) {
            $this->middleware('auth:api');
        }
    }

    /**
     * Undocumented function
     * Mô tả các lỗi validate
     */
    public function messages()
    {
        return [
            'required' => ':attribute không được để trống',
            'unique'   => ':attribute đã tồn tại',
        ];
    }

    /**
     * Undocumented function
     * Mô tả các field validate
     */
    public function attributes()
    {
        return [
            'cb_name'        => 'Tên khách hàng',
            'cb_phone'       => 'Số điện thoại',
            'cd_customer_id' => 'Khách hàng',
            'cd_rating'      => 'Điểm số',
            'project_id'     => 'Dự án',
        ];
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $per_page = $request->input('per_page', 10);
        $per_page = $per_page > 0 ? $per_page : 10;

        $type    = $request->input('type', 'article');
        $groupBy = $request->input('groupBy', '');

        $columns = (new CampaignAssign)->getTableColumns();

        $unset = ['user_id', 'deleted_at'];

        $allowFields = [];
        foreach ($columns as $column) {
            if (!in_array($column, $unset)) {
                $allowFields[] = $column;
            }
        }

        $select    = $this->_select($request, $allowFields);
        $condition = $this->_filters($request, $columns);
        $order_by  = $this->_sort($request, $columns);

        $campaign_assign = CampaignAssign::select($select)
            ->where('status', 1)
            ->where($condition)
            ->orderByRaw($order_by)
            ->paginate($per_page);

        if ($groupBy) {
            $campaign_assign = $campaign_assign->groupBy($groupBy);
        }

        return CampaignAssignResource::collection($campaign_assign);
    }

    public function show(Request $request)
    {
        $id = (int) $request->id;
        try {
            $assign = CampaignAssign::where('id', $id)->where('status', 1)->first();

            $assign->load('campaign');
            return new CampaignAssignResource($assign);

        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'errors' => [
                    [
                        'code'   => 11001,
                        'title'  => 'Record not found',
                        'detail' => 'Record ID ' . $id . ' for ' . str_replace('App\\', '', $exception->getModel()) . ' not found',
                    ],
                ],
            ])->setStatusCode(400);
        }
    }

    public function save_add_diary(Request $request)
    {
        // return $request;
//        dd($request);
        $rules = [
            'rating' => 'required',
        ];

        $id = $request->id;

        $validator = Validator::make($request->all(), $rules, $this->messages(), $this->attributes());
        $errors    = $validator->messages();

        $campaign_assign = CampaignAssign::find($id);
        if (!$campaign_assign) {
            $errors->add('assign_id', 'Khách hàng phản hồi không chính xác!');
        }
        // return $errors;
        if ($errors->toArray()) {
            return respone()->json(['mesager' => $errors]);
        }

        if (!$request->has('_validate')) {
            $data = $request->all();
            // return $data;
            $chech_log = empty($campaign_assign->logs) ? true : false;

            $params = [
                'customer_name'  => $request->customer_name ?: $campaign_assign->customer_name,
                'customer_phone' => $request->customer_phone ?: $campaign_assign->customer_phone,
                'customer_email' => $request->customer_email ?: $campaign_assign->customer_email,
            ];

            $data = array_merge($data, $params);

            $logs = $campaign_assign->logs;

            $logs[] = [
                'edit_by' => $request->user_name,
                'edit_at' => Carbon::now(),
                'approve' => 0,
                'content' => $data,
                'role' => 1
            ];

            $input = [
                'logs'        => $logs,
                'feedback'    => 1,
                'check_diary' => 1,
                'role' => 1
            ];

            if ($request->project_id != $campaign_assign->campaign->project_id) {
                $input['feedback'] = 1;
            }

            $update = $campaign_assign->fill($input)->save();

            // Cập nhật số lượng phản hồi khách hàng của chiến dịch
            if ($update) {
                if ($chech_log) {
                    $campaign = Campaign::where('id', $campaign_assign->campaign->id)->increment('feedback', 1);
                }
                $msg = "Thành công";
            } else {
                $msg = "Không cập nhật được nhật ký";
            }

            $res = [
                'status' => true,
                'msg'    => $msg,
            ];

            return response()->json($res);
        }
       
    }
    public function getAllByStaffCount(Request $request,$staff_account)
    {
        // $data = CampaignAssign::all();
        $size = $request->input('size',10);
        $data = CampaignAssign::where('staff_account',$staff_account)->paginate($size);
        return CampaignAssignResource::collection($data);
    }
    /*public function index_test(Request $request)
    {
        $id    = (int) $request->id;
        $camp_assign = new CampaignAssign();
        $check = CampaignAssign::is_exist(['user_id' => $id, 'campaign_id' => $request->campaign_id]);
        if($check){
            $msg = "Trùng";
        }else{
            $params   = [
                'campaign_id'            => '0',
                'user_id'      => $id,
            ];
            $camp_assign->fill($params)->save();
            $msg = "add thành công";
        }
        $res = [
            'status' => false,
            'msg' => $msg
            ];

        return response()->json(['data' => $res]);
    }*/
}
