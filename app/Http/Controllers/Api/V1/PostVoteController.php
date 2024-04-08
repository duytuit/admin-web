<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Post;
use App\Models\PostVote;
use Illuminate\Http\Request;

class PostVoteController extends Controller
{
    public function add(Request $request)
    {
        return $this->vote($request, true);
    }

    public function remove(Request $request)
    {
        return $this->vote($request, false);
    }

    protected function vote(Request $request, $insert = true)
    {
        $rating = $request->rating;

        $post = $this->getPost($request);
        $user = $this->getApiUser();

        // delete vote if exist
        $this->delete($post, $user);

        // insert vote
        if ($insert) {
            $param = [
                'post_id'   => $post->id,
                'post_type' => $post->type,
                'user_id'   => $user->id,
                'user_type' => $user->type,
                'rating'    => $rating,
            ];
            $post_vote = new PostVote();
            $post_vote->fill($param)->save();
        }

        $post = $this->savePostResponse($post);

        return response()->json($post);
    }

    protected function delete($post, $user)
    {
        PostVote::where([
            ['post_id', $post->id],
            ['post_type', $post->type],
            ['user_id', $user->id],
            ['user_type', $user->type],
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
        $total = PostVote::where([
            ['post_id', $post->id],
            ['post_type', $post->type],
        ])->count();

        $rating = PostVote::where([
            ['post_id', $post->id],
            ['post_type', $post->type],
        ])->avg('rating');

        $response = $post->response;

        $response['vote'] = [
            'rating' => $rating,
            'total'  => $total,
        ];

        $post->response = $response;

        $post->save();

        return $post;
    }
}
