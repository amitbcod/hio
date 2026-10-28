<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class AgentStaffUser extends Authenticatable
{
    protected $table = 'agent_staff_users';

    protected $guarded = [];

    protected $hidden = ['password_hash'];

    protected $casts = [
        'last_login' => 'datetime',
        'account_reset_required' => 'boolean',
    ];

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function agent()
    {
        return $this->belongsTo(AgentAccount::class, 'agent_account_id');
    }

    public function permissions()
    {
        return $this->hasMany(AgentStaffPermission::class);
    }
}
