<?php

namespace App\Models\BdcProgressivePrice;

use App\Models\BdcProgressives\Progressives;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;
use Illuminate\Support\Facades\Cache;

class ProgressivePrice extends Model
{
    use SoftDeletes;

    use ActionByUser;
    protected $table = 'bdc_progressive_price';

    protected $fillable = [
        'name', 'from', 'to', 'price', 'progressive_id','priority_level','quantity',
        'period_quantity',
        'date_quantity',
        'price',
        'progressive_id','option'
    ];

    public function progressive()
    {
        return $this->belongsTo(Progressives::class, 'progressive_id', 'id');
    }
    public static function get_detail_progressive_price_by_progressive_id($id){

        $rs = Cache::store('redis')->get(env('REDIS_PREFIX') . 'get_detail_progressive_price_by_progressive_id'.$id);
        if($rs){
             return $rs;
        }
        $rs = self::where('progressive_id',$id)->get(); // lấy ra thông tin dự án
        if(!$rs){
             return false;
        }
         Cache::store('redis')->put(env('REDIS_PREFIX') . 'get_detail_progressive_price_by_progressive_id' . $id, $rs,60*60*24);
         return $rs;
    }
}
