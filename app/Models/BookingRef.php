<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingRef extends Model
{
    protected $table = 'booking_refs';
    protected $guarded = [];

    public function trip()
    {
        return $this->belongsTo(Trip::class, 'trip_id');
    }

    public function paymentTransaction()
    {
        return $this->belongsTo(PaymentTransaction::class, 'payment_transaction_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'booking_ref_id');
    }

    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class, 'booking_ref_id');
    }
}
