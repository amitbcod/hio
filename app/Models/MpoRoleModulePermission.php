<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MpoRoleModulePermission extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_allowed' => 'boolean',
        'can_delegate' => 'boolean',
        'approval_limit' => 'decimal:2',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
