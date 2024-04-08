<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\V1\Controller;
use App\Models\Post;
use App\Models\PostFollow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostFollowController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function add(Request $request)
    {
        return $this->follow($request, true);
    }

    public function remove(Request $request)
    {
        return $this->follow($request, false);
    }

    protected function follow(Request $request, $insert = true)
    {
        $post = $this->getPost($request);
        $user = $this->user;

        // delete follow if exist
        $this->delete($post, $user);

        // insert follow
        if ($insert) {
            PostFollow::insert([
                'post_id'   => $post->id,
                'post_type' => $post->type,
                'user_id'   => $user->id,
                'user_type' => 'customer',
            ]);
        }

        $post = $this->savePostResponse($post);

        return response()->json($post);
    }

    protected function delete($post, $user)
    {
        PostFollow::where([
            ['post_id', $post->id],
            ['post_type', $post->type],
            ['user_id', $user->id],
            ['user_type', 'customer'],
        ])->delete();
    }

    protected function getPost($request)
    {
        $post_id = $request->post_id;

        $post = Post::select(['id', 'type', 'response'])
            ->where('id', $post_id)
            ->firstOrFail();

        return $post;
    }

    protected function savePostResponse(&$post)
    {
        $total = PostFollow::where([
            ['post_id', $post->id],
            ['post_type', $post->type],
        ])->count();

        $response = $post->response;

        $response['follow'] = $total;

        $post->response = $response;

        $post->save();

        return $post;
    }
}
