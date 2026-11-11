<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatorRoleModulePermission extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_allowed' => 'boolean',
        'can_delegate' => 'boolean',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
