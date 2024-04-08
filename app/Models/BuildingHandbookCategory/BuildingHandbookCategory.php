<?php

namespace App\Models\BuildingHandbookCategory;

use Illuminate\Database\Eloquent\Model;
use App\Models\BuildingHandbook\BuildingHandbook;
use App\Models\BuildingHandbookType\BuildingHandbookType;
use App\Models\PublicUser\Users;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class BuildingHandbookCategory extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'bdc_handbook_category';
    protected $fillable = ['name', 'parent_id', 'status', 'bdc_handbook_type_id', 'bdc_building_id','avatar','phone'];
    protected $seachable = ['name'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    /**
     * Get the comments for the blog post.
     */
    public function handbooks()
    {
        return $this->hasMany(BuildingHandbook::class, 'bdc_handbook_category_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function handbook_type()
    {
        return $this->belongsTo(BuildingHandbookType::class, 'bdc_handbook_type_id');
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

        if (isset($input['handbook_categories_keyword'])) {
            $search = $input['handbook_categories_keyword'];
            $query->where(function ($q) use ($search) {
                foreach ($this->seachable as $value) {
                    $q->orWhere($value, 'LIKE', '%' . $search . '%');
                }
            });
        }
        return $query;
    }
}
