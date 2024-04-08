<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\V1\Controller;
use App\Models\Post;
use App\Models\PostEmotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PostEmotionController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function add(Request $request)
    {
        return $this->emotion($request, true);
    }

    public function remove(Request $request)
    {
        return $this->emotion($request, false);
    }

    protected function emotion(Request $request, $insert = true)
    {
        $emotion = $request->emotion;

        $post = $this->getPost($request);
        $user = $this->user;

        // delete emotion if exist
        $this->delete($post, $user);

        // insert emotion
        if ($insert) {
            PostEmotion::insert([

                'post_id'   => $post->id,
                'post_type' => $post->type,
                'user_id'   => $user->id,
                'user_type' => 'customer',
                'emotion'   => $emotion,
            ]);
        }

        $post = $this->savePostResponse($post);

        return response()->json($post);
    }

    protected function delete($post, $user)
    {
        PostEmotion::where([
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
        $rows = PostEmotion::select(DB::raw('emotion, COUNT(*) AS total'))
            ->where([
                ['post_id', $post->id],
                ['post_type', $post->type],
            ])->groupBy('emotion')->get();

        $response = $post->response;

        $response['emotion'] = [
            'like'  => 0,
            'love'  => 0,
            'haha'  => 0,
            'wow'   => 0,
            'sad'   => 0,
            'angry' => 0,
        ];

        foreach ($rows as $row) {
            $response['emotion'][$row['emotion']] = $row['total'];
        }

        $post->response = $response;

        $post->save();

        return $post;
    }
}
