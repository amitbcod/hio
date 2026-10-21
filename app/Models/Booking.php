<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopesToAdminCountry;

class Booking extends Model
{
    use ScopesToAdminCountry;

    protected $fillable = [
        'trip_id',
        'booking_ref_id',
        'operator_id',
        'total_amount',
        'status',
        'booking_type',
        'is_admin_created',
        'agent_account_id',
        'agent_staff_user_id',
        'agent_verification_status',
        'agent_verified_by',
        'agent_verified_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'is_admin_created' => 'boolean',
        'agent_verified_at' => 'datetime',
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

    public function agentAccount()
    {
        return $this->belongsTo(AgentAccount::class, 'agent_account_id');
    }

    public function agentStaffUser()
    {
        return $this->belongsTo(AgentStaffUser::class, 'agent_staff_user_id');
    }
}
