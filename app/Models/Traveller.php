<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Traveller extends Model
{
    protected $fillable = ['trip_id', 'name', 'email', 'phone', 'date_of_birth', 'relationship'];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function allocations()
    {
        return $this->hasMany(BliTravellerAllocation::class, 'traveller_id');
    }

    public function blis()
    {
        return $this->belongsToMany(BookingLineItem::class, 'bli_traveller_allocations', 'traveller_id', 'bli_id');
    }
}
