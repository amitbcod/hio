<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MpoStaffPermissionOverride extends Model
{
    protected $guarded = [];

    protected $casts = [
        'can_delegate' => 'boolean',
    ];

    public function staffUser(): BelongsTo
    {
        return $this->belongsTo(MpoStaffUser::class, 'mpo_staff_user_id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
