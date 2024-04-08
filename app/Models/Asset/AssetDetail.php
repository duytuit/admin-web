<?php

namespace App\Models\Asset;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetDetail extends Model
{
    use SoftDeletes;
    protected $table = 'asset_area_info';
    protected $guarded  = [];
    public function asset(){
        return $this->belongsTo(Asset::class, 'asset_detail_id','id');
    }
    public function office(){
        return $this->belongsTo(AssetArea::class, 'office_id','id');
    }
}
