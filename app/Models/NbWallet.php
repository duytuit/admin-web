<?php

namespace App\Models;
use App\Traits\ActionByUser;

class NbWallet extends Model
{
    protected $guarded = [];
    use ActionByUser;
    protected $table = 'nb_wallet';
    protected $fillable = [
        'user_id',
        'wallet_name',
        'currency_code',
        'wallet_description',
        'wallet_balance',
        'save_to_report',
        'created_at',
        'updated_at'
    ];

    public function users()
    {
        return $this->belongsTo(BoCustomer::class, 'user_id', 'id');
    }

    public function records()
    {
        return $this->hasMany(NbRecords::class, 'wallet_id', 'id');
    }
}