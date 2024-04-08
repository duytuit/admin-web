<?php

namespace Modules\Tasks\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class Apartment extends Model
{
    use SoftDeletes;

    use ActionByUser;
    protected $table = 'bdc_apartments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [

    ];
    
    public function building() {
        return $this->belongsTo(Building::class, 'building_id', 'id')->select('id','name', 'description', 'address', 'phone', 'email');
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

}
