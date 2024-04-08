<?php

namespace App\Http\Resources;

use App\Traits\ApiData;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    use ApiData;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        //return parent::toArray($request);

        $replies = [];
        foreach ($this->comments as $comment) {
            $replies[] = $this->getComment($comment);
        }

        $comment            = $this->getComment($this);
        $comment['replies'] = $replies;

        return $comment;
    }

    protected function getComment($comment)
    {
        return [
            'id'         => $comment->id,
            'content'    => $comment->content,
            'rating'     => $comment->rating,
            'status'     => $comment->status,
            'images'     => $comment->images,
            'created_at' => $comment->created_at,
            'updated_at' => $comment->updated_at,
            'user'       => [
                'user_id'   => $comment->user_id,
                'user_type' => $comment->user_type,
                'char'      => !empty($comment->user->char)?$comment->user->char:'U',
                'name'      => !empty($comment->user->name)?$comment->user->name:'Unknow User',
                'email'     => !empty($comment->user->email)?$comment->user->email:'Unknow Email',
                'phone'     => !empty($comment->user->phone)?$comment->user->phone:'Unknow Phone',
                'avatar'    => !empty($comment->user->avatar)?$comment->user->avatar:'Unknow Avatar',
            ],
        ];
    }
}
