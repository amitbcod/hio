<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleModulePermission extends Model
{
    protected $table = 'role_module_permissions';
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
        return $this->belongsTo(Module::class, 'module_id');
    }

    public function role()
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class, 'role_id');
    }
}
