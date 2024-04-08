<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Post;
use App\Models\PostRegister;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class PostRegisterController extends Controller
{
    public function add(Request $request)
    {
        return $this->register($request, true);
    }

    public function remove(Request $request)
    {
        return $this->register($request, false);
    }

    protected function register(Request $request, $insert = true)
    {
        $rating = $request->rating ?? 0;

        $post = $this->getPost($request);
        $user = $this->getApiUser();

        $type = $post->type;
        // Nếu sự kiện hay voucher thuộc loại nội bộ
        if ($post->private == 1) {
            if ($user->type == 'customer') {
                $check = $post->checkRegisters($user->cb_id);
                if (!$check) {
                    return response()->json(['msg' => 'Bạn không thuộc diện đăng ký tham gia sự kiện này.'])->setStatusCode(500);
                }
            } else {
                return response()->json(['msg' => 'Bạn không thuộc diện đăng ký tham gia sự kiện này.'])->setStatusCode(500);
            }
        }

        if ($type == 'voucher') {
            // Kiểm tra xem còn voucher còn không
            $count = $post->usedVoucher();
            if ($count <= 0) {
                return response()->json(['msg' => 'Hiện tại voucher đã hết.'])->setStatusCode(422);
            }
        }

        // delete register if exist
        $this->delete($post, $user);

        // insert register
        if ($insert) {
            $post_register = new PostRegister();
            $param         = [
                'post_id'   => $post->id,
                'code'      => $this->randomString(),
                'post_type' => $post->type,
                'user_id'   => $user->id,
                'user_type' => $user->type,
                'user_name' => $user->name,
            ];

            $post_register->fill($param)->save();

        }

        $post = $this->savePostResponse($post);

        return response()->json(['code' => $post_register->code]);
    }

    protected function delete($post, $user)
    {
        PostRegister::where([
            ['post_id', $post->id],
            ['post_type', $post->type],
            ['user_id', $user->id],
            ['user_type', $user->type],
        ])->delete();
    }

    protected function getPost($request)
    {
        $post_id = $request->post_id;

        $post = Post::select(['id', 'type', 'response', 'private', 'notify', 'number'])
            ->where('id', $post_id)
            ->firstOrFail();

        return $post;
    }

    protected function savePostResponse(&$post)
    {
        $total = PostRegister::where([
            ['post_id', $post->id],
            ['post_type', $post->type],
        ])->count();

        $response = $post->response;

        $response['register'] = $total;

        $post->response = $response;

        $post->save();

        return $post;
    }

    protected function randomString($length = 6)
    {
        $str        = "";
        $characters = array_merge(range('A', 'Z'), range('0', '9'));
        $max        = count($characters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $rand = mt_rand(0, $max);
            $str .= $characters[$rand];
        }
        return $str;
    }

    public function checkIn(Request $request)
    {
        $user    = $this->getApiUser();
        $post_id = $request->post_id;
        $posts = Post::where('id',$post_id)->first();
        $time_checkin = time();
        $start_post = strtotime($posts['start_at']);
        $end_post = strtotime($posts['end_at']);
        $errors = [];
        if($start_post <= $time_checkin && $time_checkin <= $end_post){
//            Cache::store('redis')->put('trung', 'nằm trong khoảng thời gian checkin');
            $register = PostRegister::where('post_id', $post_id)
                ->where('user_id', $user->id)
                ->where('user_type', $user->type)
                ->first();

            if (!$register) {
                $errors[] = "Không thành công. Bạn vui lòng kiểm tra lại.";
            } else {
                if ($register->check_in) {
                    $errors[] = "Mã đã được sử dụng.";
                }
            }
        }else{
//            Cache::store('redis')->put('trung', 'Không nằm trong khoảng thời gian checkin');
            $errors[] = "Đã quá thời gian checkin";
        }
//        Cache::store('redis')->put('trung', $errors);
//        dd($errors);
        if ($errors) {
            return response()->json(['errors' => $errors])->setStatusCode(302);

        } else {
            $register->check_in = Carbon::now();
            $register->save();
            return response()->json(['msg' => 'Check in thành công']);
        }
    }
}
