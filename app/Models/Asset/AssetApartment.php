<?php

namespace App\Models\Asset;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Building\BuildingPlace;
use App\Models\PublicUser\Users;
use App\Models\Apartments\Apartments;
use App\Models\Category;
use App\Traits\ActionByUser;

class AssetApartment extends Model
{
    use SoftDeletes;

    use ActionByUser;
    protected $table = 'bdc_asset_apartments';

    protected $fillable = ['bdc_building_id','bdc_apartment_id', 'code', 'name', 'description', 'documents','building_place_id', 'asset_category_id','created_by','updated_by'];

    public function building_place()
    {
        return $this->belongsTo(BuildingPlace::class, 'building_place_id','id');
    }
    public function asset_category()
    {
        return $this->belongsTo(Category::class, 'asset_category_id','id');
    }
    public function apartment()
    {
        return $this->belongsTo(Apartments::class, 'bdc_apartment_id','id');
    }
    public function user_created_by()
    {
        return $this->belongsTo(Users::class, 'created_by','id');
    }
    public function user_updated_by()
    {
        return $this->belongsTo(Users::class, 'updated_by','id');
    }

}
