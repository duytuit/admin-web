<?php

namespace App\Models;

use App\Models\Branch;
use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\MyActivityTraits;

class Partner extends Model
{
    use SoftDeletes,
        MyActivityTraits;

    protected $guarded = [];
    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;

    public function branches()
    {
        return $this->hasMany(Branch::class, 'partner_id', 'id');
    }
}
