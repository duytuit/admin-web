<?php

namespace App\Models\BdcApartmentDebit;

use App\Models\Apartments\Apartments;
use App\Models\Building\Building;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class ApartmentDebit extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'bdc_apartment_debit';

    protected $fillable = [
        'bdc_building_id', 'bdc_apartment_id', 'debit_period_code', 'name', 'old_owed', 'new_owed', 'total', 'total_paid', 'total_free'
    ];

    public function building()
    {
        return $this->belongsTo(Building::class, 'bdc_building_id', 'id');
    }

    public function apartment()
    {
        return $this->belongsTo(Apartments::class, 'bdc_apartment_id', 'id');
    }
}
