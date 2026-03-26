<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class TravelerAccount extends Authenticatable
{
    protected $table = 'traveler_accounts';
    protected $guarded = [];
    public $timestamps = true;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'terms_accepted_at' => 'datetime',
        'privacy_accepted_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password_reset_requested_at' => 'datetime',
        'marketing_opt_in' => 'boolean',
        '2fa_enabled' => 'boolean',
        'account_suspended' => 'boolean',
        'communication_preference' => 'array',
    ];

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function profile()
    {
        return $this->hasOne(TravelerProfile::class, 'traveler_account_id');
    }
}
