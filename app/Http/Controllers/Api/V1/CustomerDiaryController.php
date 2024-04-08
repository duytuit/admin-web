<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use App\Models\Campaign;
use App\Models\CampaignAssign;
use App\Models\CustomerDiary;
use App\Models\Filter;
use DB;
use Illuminate\Http\Request;
use Validator;

class CustomerDiaryController extends Controller
{

    public function __construct()
    {
        $this->model = new CustomerDiary();
    }

    public function attributes()
    {
        return [
            'cd_rating' => 'Điểm số',
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

        $columns = (new CustomerDiary)->getTableColumns();

        $unset = ['cd_user_id'];

        $allowFields = [];
        foreach ($columns as $column) {
            if (!in_array($column, $unset)) {
                $allowFields[] = $column;
            }
        }

        $select    = $this->_select($request, $allowFields);
        $condition = $this->_filters($request, $columns);
        $order_by  = $this->_sort($request, $columns);

        $partners = CustomerDiary::select($select)
            ->where($condition)
            ->orderByRaw($order_by)
            ->paginate($per_page);

        if ($partners->toArray()) {
            $data = [
                'status' => true,
                'code'   => 200,
                'data'   => $partners,
            ];
        } else {
            $data = [
                'status'   => false,
                'code'     => 400,
                'messager' => "Lỗi do hệ thống",
            ];
        }

        return response()->json($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $id = (int) $request->id;
        try {
            $customer_diary = CustomerDiary::findOrFail($id);

            $data = [
                'status' => true,
                'code'   => 200,
                'data'   => $customer_diary,
            ];

            return response()->json($data, 200);

        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'errors' => [
                    [
                        'code'   => 11001,
                        'title'  => 'Record not found',
                        'detail' => 'Record ID ' . $id . ' for ' . str_replace('App\\', '', $exception->getModel()) . ' not found',
                    ],
                ],
            ])->setStatusCode(401);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request)
    {
        $rules = [
            'cd_rating' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules, [], $this->attributes());
        $errors    = $validator->messages();

        if ($errors->toArray()) {
            return response()->json(['errors' => $errors])->setStatusCode(401);
        }

        if (!$request->has('_validate')) {

            try {
                $data           = $request->only('cd_rating', 'project_id', 'status', 'cd_description', 'cd_customer_id', 'filters', 'campaign_assign_id');
                $id             = (int) $request->id;
                $customer_diary = CustomerDiary::findOrNew($id);

                $params = [
                    'cd_id' => time(),
                ];

                $data = array_merge($data, $params);
                $customer_diary->fill($data);
                $assign_id = $request->input('campaign_assign_id', 0);

                DB::transaction(function () use ($customer_diary, $assign_id) {
                    $customer_diary->save();
                    Filter::updateNumber($customer_diary->filters, 'customer');
                    $this->updateCampaign($customer_diary);
                });

                $data = [
                    'status'   => true,
                    'code'     => 200,
                    'messager' => "Cập nhật nhật ký thành công.",
                    'data'     => $customer_diary,
                ];

                return response()->json($data);

            } catch (ModelNotFoundException $exception) {
                return response()->json([
                    'status'   => false,
                    'code'     => 401,
                    'messager' => "Cập nhật nhật ký thất bại",
                ])->setStatusCode(401);
            }
        }
    }

    protected function updateCampaign($customer_diary)
    {
        $assign_id = $customer_diary->campaign_assign_id;
        $count     = CustomerDiary::where('campaign_assign_id', $assign_id)->count();

        if ($count < 2) {
            $assign = CampaignAssign::findOrFail($assign_id);
            // Cập nhật trạng thái phản hồi khách hàng
            $assign->check_diary = 1;
            $assign->save();
            // Cập nhật số lượng phản hồi khách hàng của chiến dịch
            $campaign       = Campaign::findOrFail($assign->campaign->id);
            $index_assigned = CampaignAssign::count_assign_campaign($campaign->id);
            $count_status   = CampaignAssign::where('campaign_id', $campaign->id)->where('feedback', 1)->count();

            $params = [
                'feedback' => $index_assigned,
                'diary_id' => $customer_diary->id,
                'status'   => $count_status,
            ];
            $campaign->fill($params);
            $campaign->save();
        }
    }
}
