<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class AgentAccount extends Authenticatable
{
    protected $table = 'agent_accounts';

    protected $guarded = [];

    protected $hidden = ['password_hash'];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function ownerVerifications()
    {
        return $this->hasMany(ControllerVerification::class, 'requester_agent_account_id');
    }

    public function profile()
    {
        return $this->hasOne(AgentProfile::class);
    }

    public function complianceDocuments()
    {
        return $this->hasMany(AgentComplianceDocument::class);
    }

    public function staffUsers()
    {
        return $this->hasMany(AgentStaffUser::class);
    }

    public function setupProgress()
    {
        return $this->hasOne(AgentSetupProgress::class);
    }

    public function operations()
    {
        return $this->hasOne(AgentOperation::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'agent_account_id');
    }
}
