<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\SocialPost;
use App\Models\SocialReaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SocialPostReactionController extends Controller
{
    public function add(Request $request)
    {
        //validate
        $validator = $this->validateReactionData($request);

        if ($validator->fails()) {
            $error = $validator->errors();
            return $this->validateFail($error->first(), $error->toArray());
        }

        return $this->reaction($request, true);
    }

    public function remove(Request $request)
    {
        return $this->reaction($request, false);
    }

    protected function reaction(Request $request, $insert = true)
    {
        $emotion = $request->emotion;
        $post_id = $request->post_id;

        $social_post = $this->getSocialPost($post_id);

        $user = $this->getApiUser();

        // delete emotion if exist
        $this->delete($social_post->id, $user->id);

        // insert emotion
        if ($insert) {
            $data = [
                'post_id' => $social_post->id,
                'user_id' => $user->id,
                'emotion' => $emotion,
            ];
            $item = new SocialReaction();
            $item->fill($data)->save();
        }

        $social_post = $this->saveSocialPostResponse($social_post);

        $item = $social_post->toArray();
        $item['updated_at'] = $social_post->updated_at;

        return $this->responseSuccess($item);
    }

    protected function delete($post_id, $user_id)
    {
        return SocialReaction::where([
            ['post_id', $post_id],
            ['user_id', $user_id],
        ])->delete();
    }

    protected function getSocialPost($post_id)
    {
        $social_post = SocialPost::select(['id', 'response'])
            ->where('id', $post_id)
            ->firstOrFail();

        return $social_post;
    }

    protected function saveSocialPostResponse(&$social_post)
    {
        $rows = SocialReaction::select(DB::raw('emotion, COUNT(*) AS total'))
            ->where('post_id', $social_post->id)->groupBy('emotion')->get();

        $response = $social_post->response;

        $response['emotion'] = [
            'like' => 0,
            'love' => 0,
            'haha' => 0,
            'wow' => 0,
            'sad' => 0,
            'angry' => 0,
        ];

        foreach ($rows as $row) {
            $response['emotion'][$row['emotion']] = $row['total'];
        }

        $social_post->response = $response;

        $social_post->save();

        return $social_post;
    }

    protected function validateReactionData($request)
    {
        $rules = [
            'emotion' => [
                'required',
                Rule::in(['like', 'love', 'haha', 'wow', 'sad', 'angry']),
            ],
        ];
        $messages = [];
        $attributes = [];

        $validator = Validator::make($request->all(), $rules, $messages, $attributes);

        return $validator;
    }
}
