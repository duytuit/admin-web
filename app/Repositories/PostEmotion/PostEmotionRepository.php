<?php

namespace App\Repositories\PostEmotion;

use App\Repositories\Eloquent\Repository;
use App\Models\PostEmotion\PostEmotion;

class PostEmotionRepository extends Repository {

    const USE_POST = 1;
    const NOT_USE_POST = 0;

    const EMOTION = ['like', 'love', 'haha', 'wow', 'sad', 'angry'];

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return PostEmotion::class;
    }

    public function findEmotion($post_id, $user_id)
    {
        return $this->model->where([
            'post_id' => $post_id,
            'user_id' => $user_id
        ])->first();
    }
    public function deleteEmo($post_id, $user_id)
    {
        return $this->model->where([
            'post_id' => $post_id,
            'user_id' => $user_id
        ])->delete();
    }

}
