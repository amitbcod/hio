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
}
