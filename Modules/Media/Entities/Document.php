<?php

namespace Modules\Media\Entities;

use App\Models\ApartmentGroups\ApartmentGroup;
use App\Models\Apartments\Apartments;
use App\Models\Building\Building;
use Illuminate\Database\Eloquent\Model;
use App\Traits\ActionByUser;

class Document extends Model
{

    use ActionByUser;
    protected $table = "bdc_documents";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
        'user_id',
        'bdc_building_id',
        'attach_file'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    const TYPE_BUILDING = 1;
    const TYPE_APARTMENT = 2;
    const TYPE_APARTMENT_GROUP = 3;

    public function buildings()
    {
        return $this->morphedByMany(Building::class,'documentable');
    }

    public function apartments()
    {
        return $this->morphedByMany(Apartments::class,'documentable');
    }

    public function apartmentGroups()
    {
        return $this->morphedByMany(ApartmentGroup::class, 'documentable');
    }
}
