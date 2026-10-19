<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentStaffPermission extends Model
{
    protected $guarded = [];

    protected $casts = [
        'can_read' => 'boolean',
        'can_create' => 'boolean',
        'can_update' => 'boolean',
        'can_approve' => 'boolean',
        'can_publish' => 'boolean',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
