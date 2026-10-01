<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'booking_id',
        'booking_ref_id',
        'amount',
        'method',
        'status',
        'transaction_ref',
        'payment_id',
        'settlement_status',
        'bank_transfer_date',
        'bank_transfer_receipt_path',
        'admin_notes',
        'submitted_by',
        'submitted_at',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'bank_transfer_date' => 'date',
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function bookingRef()
    {
        return $this->belongsTo(BookingRef::class, 'booking_ref_id');
    }
}
