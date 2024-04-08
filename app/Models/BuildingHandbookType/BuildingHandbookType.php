<?php

namespace App\Models\BuildingHandbookType;

use Illuminate\Database\Eloquent\Model;
use App\Models\BuildingHandbook\BuildingHandbook;
use App\Models\PublicUser\Users;
use App\Models\Building\Building;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class BuildingHandbookType extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'bdc_handbook_type';
    protected $fillable = ['name', 'bdc_building_id','type_company'];
    /**
     * Get the comments for the blog post.
     */
    public function handbooks()
    {
        return $this->hasMany(BuildingHandbook::class, 'bdc_handbook_type_id');
    }
    public function handbook_type_building()
    {
        return $this->belongsTo(Building::class, 'bdc_building_id');
    }
    public function getDateFormat()
    {
        return 'Y-m-d H:i:s.u';
    }
    
    public function author()
    {
        return $this->belongsTo(Users::class);
    }

    public function scopeFilter($query, $input)
    {
        foreach ($this->fillable as $value) {
            if (isset($input[$value])) {
                $query->where($value, $input[$value]);
            }
        }

        if (isset($input['keyword'])) {
            $search = $input['keyword'];
            $query->where(function ($q) use ($search) {
                foreach ($this->fillable as $value) {
                    $q->orWhere($value, 'LIKE', '%' . $search . '%');
                }
            });
        }
        return $query;
    }
}
