<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentSetupProgress extends Model
{
    protected $guarded = [];

    protected $casts = [
        'profile_complete' => 'boolean',
        'compliance_complete' => 'boolean',
        'users_complete' => 'boolean',
        'operations_complete' => 'boolean',
    ];

    public function agent()
    {
        return $this->belongsTo(AgentAccount::class, 'agent_account_id');
    }
}
