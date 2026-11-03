<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Concerns\ScopesToAdminCountry;

class PaymentTransaction extends Model
{
    use ScopesToAdminCountry;

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

    public function scopePaymentComplete(Builder $query): Builder
    {
        return $query->where(function (Builder $paidQuery): void {
            $paidQuery->where('status', 'paid')
                ->orWhere(function (Builder $settlementQuery): void {
                    $settlementQuery->where('settlement_status', 'verified_settled')
                        ->whereNotIn('status', ['failed', 'refunded']);
                });
            });
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function bookingRef()
    {
        return $this->belongsTo(BookingRef::class, 'booking_ref_id');
    }
}
