<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    protected $fillable = ['traveler_account_id', 'title', 'start_date', 'end_date', 'status'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function traveler()
    {
        return $this->belongsTo(TravelerAccount::class, 'traveler_account_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function travellers()
    {
        return $this->hasMany(Traveller::class);
    }
}
