<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\V1\Controller;
use App\Models\PollOption;
use App\Models\Post;
use App\Models\PostPoll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostPollController extends Controller
{
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $id           = (int) $request->post_id;
        $poll_options = $request->poll_options;

        $post      = Post::findOrFail($id);
        $user      = Auth::user();
        $option_id = [];

        $all_id_options = PollOption::pluck('id')->toArray();

        foreach ($poll_options as $poll_option) {
            if (!in_array($poll_option['id'], $all_id_options)) {
                $error = 'Câu hỏi không tồn tại.';
                return response()->json([
                    'msg' => $error,
                ])->setStatusCode(500);
            } else {
                $option_id[] = $poll_option['id'];
            }
        }

        $options = PollOption::whereIn('id', $option_id)->get();
        foreach ($poll_options as $poll_option) {
            foreach ($options as $option) {
                $poll_keys = array_keys($option->options);
                if ($poll_option['id'] == $option->id && in_array($poll_option['option'], $poll_keys)) {
                    $post_poll = PostPoll::where('post_id', $id)
                        ->where('poll_id', $option->id)
                        ->where('user_id', $user->id)
                        ->first();

                    if ($post_poll) {
                        $post_poll->poll_key = $poll_option['option'];
                        $post_poll->save();
                    } else {
                        $param = [
                            'post_id'   => $id,
                            'poll_id'   => $poll_option['id'],
                            'post_type' => $post->type,
                            'user_id'   => $user->id,
                            'user_type' => $user->type,
                            'user_name' => $user->name,
                            'poll_key'  => $poll_option['option'],
                        ];

                        $post_poll = new PostPoll();
                        $post_poll->fill($param)->save();
                    }

                    $post = $this->savePostResponse($post);
                }
            }
        }

        return response()->json([
            'data' => $post->response,
            'msg'  => 'Gửi ý kiến thành công!',
        ]);
    }

    protected function savePostResponse(&$post)
    {
        $post_polls = PostPoll::where('post_id', $post->id)
            ->where('post_type', $post->type)
            ->get()
            ->groupBy('poll_id');

        foreach ($post_polls as $key => $post_poll) {
            $option_key = [];
            foreach ($post_poll as $value) {
                $option_key[] = $value->poll_key;
            }
            $itemCount[$key] = array_count_values($option_key);
        }

        $poll_key     = [];
        $post_options = PollOption::whereIn('id', $post->poll_options)->get();
        foreach ($post_options as $value) {
            foreach ($value->options as $index => $item) {
                $poll_key[$value->id][$index] = 0;
            }

        }

        foreach ($poll_key as $key => $value) {
            foreach ($itemCount as $index => $item) {
                if ($key == $index) {
                    $itemCount[$index] = array_merge($value, $item);
                }
            }
        }

        $total = PostPoll::where('post_id', $post->id)->get()->groupBy('user_id');
        $poll  = [
            'total' => count($total),
            'polls' => $itemCount,
        ];

        $response = $post->response;

        $response['poll'] = $poll;

        $post->response = $response;

        $post->save();

        return $post;
    }
}
