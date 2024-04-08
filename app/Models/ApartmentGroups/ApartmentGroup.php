<?php

namespace App\Models\ApartmentGroups;

use App\Models\Apartments\Apartments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Media\Entities\Document;
use App\Traits\ActionByUser;

class ApartmentGroup extends Model
{
    use SoftDeletes;

    use ActionByUser;
    protected $table = 'bdc_apartment_groups';

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

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function apartments()
    {
        return $this->hasMany(Apartments::class,'bdc_apartment_group_id');
    }
}
