<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentComplianceDocument extends Model
{
    protected $guarded = [];

    public function agent()
    {
        return $this->belongsTo(AgentAccount::class, 'agent_account_id');
    }
}
