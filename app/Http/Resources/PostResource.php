<?php

namespace App\Http\Resources;

use App\Traits\ApiData;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
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
        return parent::toArray($request);
    }
}
