<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TravelerCart extends Model
{
    protected $table = 'traveler_carts';

    protected $fillable = [
        'traveler_account_id',
        'items',
    ];

    protected $casts = [
        'items' => 'array',
    ];

    public function travelerAccount()
    {
        return $this->belongsTo(TravelerAccount::class, 'traveler_account_id');
    }
}
