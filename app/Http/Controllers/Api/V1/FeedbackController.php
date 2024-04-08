<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\FeedbackResource;
use App\Models\BoCustomer;
use App\Models\BoUser;
use App\Models\CustomerDiary;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;

class FeedbackController extends Controller
{

    public function attributes()
    {
        return [
            'title'   => 'Tiêu đề',
            'content' => 'Nội dung',
            'type'    => 'Loại feedback',
            'rating'  => 'Số sao',
            'user_id' => 'Nhân viên',
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
        $per_page = $request->input('per_page', 10);
        $per_page = $per_page > 0 ? $per_page : 10;

        $columns = (new Feedback)->getTableColumns();

        $unset = ['customer_id', 'deleted_at'];

        $allowFields = [];
        foreach ($columns as $column) {
            if (!in_array($column, $unset)) {
                $allowFields[] = $column;
            }
        }

        $user      = $this->getApiUser();
        $select    = $this->_select($request, $allowFields);
        $condition = $this->_filters($request, $columns);
        $order_by  = $this->_sort($request, $columns);

        $feedback = Feedback::select($select)
            ->where('customer_id', $user->id)
            ->where($condition)
            ->orderByRaw($order_by)
            ->paginate($per_page);

        return FeedbackResource::collection($feedback);
    }

    /**
     * Xem bản ghi
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function show(Request $request)
    {
        $id   = (int) $request->id;
        $user = $this->getApiUser();

        $feedback = Feedback::where('id', $id)
            ->where('customer_id', $user->id)
            ->first();
        if ($feedback) {
            $feedback->load('comments.user');
            $comments = [];
            foreach ($feedback->comments as $key => $comment) {
                if ($comment->user) {
                    switch ($comment->user_type) {
                        case BoUser::class:
                            $user = [
                                'id'     => $comment->user->id,
                                'name'   => $comment->user->ub_title,
                                'avatar' => $comment->user->ub_avatar,
                            ];

                            break;
                        case BoCustomer::class:
                            $user = [
                                'id'     => $comment->user->id,
                                'name'   => $comment->user->cb_name,
                                'avatar' => $comment->user->cb_avatar,
                            ];
                            break;
                        default:
                            break;
                    }
                    $comment         = $comment->toArray();
                    $comment['user'] = $user;
                }
                $comments[$key] = $comment;
            }
            $feedback             = $feedback->toArray();
            $feedback['comments'] = $comments;

            return new FeedbackResource($feedback);
        } else {
            $message = [
                'error'  => 1,
                'status' => '404',
                'msg'    => 'Không tìm thấy phản hổi này.',
            ];

            return response()->json($message)->setStatusCode(404);
        }

    }

    /**
     * Lưu thông tin khách hàng phản hồi.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $cb_id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $rules = [
            'title'   => 'required',
            'content' => 'required',
            'type'    => 'required|in:user,product,service,other',
        ];

        if ($request->type == 'user') {
            $rules['rating']  = 'required';
            $rules['user_id'] = 'required';
        }

        $validator = Validator::make($request->all(), $rules, [], $this->attributes());
        $errors    = $validator->messages();

        // Kiểm tra xem khách hàng đã được nhân viên này chăm sóc hay chưa để đánh giá
        $customer = $this->getApiUser();
        $user_ids = CustomerDiary::where('cd_customer_id', $customer->cb_id)->pluck('cd_user_id')->toArray();
        $users    = BoUser::whereIn('ub_id', $user_ids)->where('ub_status', 1)->pluck('ub_id')->toArray();
        if (!in_array($request->user_id, $users) && $request->type == 'user') {
            $errors->add('user_id', 'Hiện tại bạn chưa thể đánh giá hay phản hồi về nhân viên này.');
        }

        if ($errors->toArray()) {
            return response()->json(['error' => $errors])->setStatusCode(422);
        }

        if (!$request->has('_validate')) {
            $input = $request->except(['_token']);

            $input['status']      = $request->input('status', 0);
            $input['content']     = strip_tags($request->input('content'));
            $input['customer_id'] = $customer->id;

            $param  = [];
            $files  = $request->input('files', '');
            $images = $request->input('images', '');

            if ($files) {
                $param['files'] = $files;
            }
            if ($images) {
                $param['images'] = $images;
            }

            if (array_key_exists('files', $input)) {
                unset($input['files']);
            }

            if (array_key_exists('images', $input)) {
                unset($input['images']);
            }

            if ($param) {
                $input['attached'] = $param;
            }

            $feedback = new Feedback();
            $feedback->forceFill($input)->save();

            if ($request->type == 'user') {
                $this->rating_average($request->user_id);
            }

            if ($feedback) {
                $message = [
                    'error'  => 0,
                    'status' => 'success',
                    'msg'    => 'Cảm ơn quý khách đã đóng góp ý kiến! Chúng tôi sẽ tiếp nhân và phản hồi lại quý khách hàng sớm nhất.',
                ];

                return response()->json($message);
            }
        }

    }

    // Tính điểm sao trung bình khi có đánh giá nhân viên
    public function rating_average($usre_id)
    {
        $ratings = Feedback::where('user_id', $usre_id)->pluck('rating');
        $sum     = 0;
        $i       = 0;
        foreach ($ratings as $value) {
            $sum = $sum + $value;
            $i += 1;
        }

        $average = round($sum / $i, 1);

        $user = BoUser::findOrFail($usre_id);

        $user->rating = $average;
        $user->save();
    }
}
