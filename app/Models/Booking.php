<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = ['trip_id', 'operator_id', 'total_amount', 'status'];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
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
