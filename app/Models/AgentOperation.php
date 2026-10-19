<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentOperation extends Model
{
    protected $table = 'agent_operations';

    protected $guarded = [];

    protected $casts = ['bank_details' => 'encrypted:array'];

    public function agent()
    {
        return $this->belongsTo(AgentAccount::class, 'agent_account_id');
    }
}
