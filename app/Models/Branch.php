<?php

namespace App\Models;

use App\Models\Model;
use App\Traits\MyActivityTraits;

class Branch extends Model
{
    use MyActivityTraits;

    protected $guarded = [];
    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;

}
