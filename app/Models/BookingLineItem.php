<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingLineItem extends Model
{
    protected $fillable = ['booking_id', 'service_type', 'service_id', 'transport_booking_id', 'trip_type', 'quantity', 'price', 'start_date', 'end_date', 'status'];

    protected $casts = [
        'price' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function allocations()
    {
        return $this->hasMany(BliTravellerAllocation::class, 'bli_id');
    }

    public function travellers()
    {
        return $this->belongsToMany(Traveller::class, 'bli_traveller_allocations', 'bli_id', 'traveller_id');
    }

    public function transportBooking()
    {
        return $this->belongsTo(TransportBooking::class, 'transport_booking_id');
    }
}
