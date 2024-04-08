<?php

namespace App\Models\Building;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ActionByUser;

class Company extends Model
{
    use ActionByUser;
    protected $table = 'bdc_company';

    protected $fillable = [
        'name', 'code', 'type', 'admin_id','customer_code_prefix'
    ];

    public function staff()
    {
        return $this->hasMany(CompanyStaff::class, 'bdc_company_id');
    }

    public function building()
    {
        return $this->hasMany(Building::class, 'company_id');
    }

    public function admin()
    {
        return $this->belongsTo(CompanyStaff::class, 'admin_id');
    }
}
