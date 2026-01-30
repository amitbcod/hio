<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatorRoleAccessMapping extends Model
{
    protected $table = 'operator_role_access_mapping';

    protected $fillable = [
        'user_id',
        'role',
        'module',
        'can_read',
        'can_create',
        'can_update',
        'can_approve',
        'can_publish',
        'capacity_level',
        'notes',
    ];

    protected $casts = [
        'can_read'    => 'boolean',
        'can_create'  => 'boolean',
        'can_update'  => 'boolean',
        'can_approve' => 'boolean',
        'can_publish' => 'boolean',
    ];
}
