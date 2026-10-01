<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = ['trip_id', 'booking_ref_id', 'operator_id', 'total_amount', 'status', 'booking_type', 'is_admin_created'];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'is_admin_created' => 'boolean',
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function bookingRef()
    {
        return $this->belongsTo(BookingRef::class);
    }

    public function lineItems()
    {
        return $this->hasMany(BookingLineItem::class);
    }

    public function payments()
    {
        return $this->hasMany(PaymentTransaction::class);
    }
}
