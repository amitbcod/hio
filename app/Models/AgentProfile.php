<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentProfile extends Model
{
    protected $guarded = [];

    protected $casts = ['focus_segments' => 'array'];

    public function agent()
    {
        return $this->belongsTo(AgentAccount::class, 'agent_account_id');
    }
}
