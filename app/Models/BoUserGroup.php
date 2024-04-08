<?php

namespace App\Models;

use App\Models\Model;

class BoUserGroup extends Model
{
    protected $appends = ['title'];

    public function getTitleAttribute()
    {
        return $this->gb_title;
    }
}
