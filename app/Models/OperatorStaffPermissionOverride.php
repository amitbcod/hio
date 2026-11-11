<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatorStaffPermissionOverride extends Model
{
    protected $guarded = [];

    protected $casts = [
        'can_delegate' => 'boolean',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function staffUser()
    {
        return $this->belongsTo(OperatorUser::class, 'operator_user_id');
    }
}
