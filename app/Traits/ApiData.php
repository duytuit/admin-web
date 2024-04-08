<?php

namespace App\Traits;

trait ApiData
{
    /**
     * Set one resource
     *
     * @param  mixed  $resource
     * @return void
     */
    public function one($item)
    {
        $this->resource = $item;
        return $this;
    }

    /**
     * Set many resource
     *
     * @param  mixed  $resource
     * @return void
     */
    public function many($items)
    {
        return self::collection($items);
    }
}
