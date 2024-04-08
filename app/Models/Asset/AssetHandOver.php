<?php

namespace App\Models\Asset;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Apartments\Apartments;
use App\Models\PublicUser\Users;
use App\Traits\ActionByUser;

class AssetHandOver extends Model
{
    use SoftDeletes;

    use ActionByUser;
    protected $table = 'bdc_asset_hand_overs';

    protected $fillable = ['bdc_building_id', 'asset_apartment_id', 'apartment_id', 'handover_person_id', 'date_expected', 'date_of_delivery',
    'warranty_period','customer','email','phone', 'description', 'documents', 'status','updated_by'];
   // protected $guarded = [];

   public function apartment()
   {
       return $this->belongsTo(Apartments::class, 'apartment_id','id');
   }
   public function asset()
   {
       return $this->belongsTo(AssetApartment::class, 'asset_apartment_id','id');
   }
   public function user()
   {
       return $this->belongsTo(Users::class, 'handover_person_id','id');
   }
   public function user_updated_by()
   {
       return $this->belongsTo(Users::class, 'updated_by','id');
   }
}
