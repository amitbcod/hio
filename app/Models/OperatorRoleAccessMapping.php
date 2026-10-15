<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopesToAdminCountry;

class OperatorRoleAccessMapping extends Model
{
    use ScopesToAdminCountry;
    protected $table = 'operator_role_access_mapping';

    protected $fillable = [
        'user_id',
        'role',
        'module', // store module slug
        'can_read',
        'can_create',
        'can_update',
        'can_approve',
        'can_publish',
        'capacity_level',
        'notes',
        'business_id',
    ];

    // Relations
    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    protected $casts = [
        'can_read'    => 'boolean',
        'can_create'  => 'boolean',
        'can_update'  => 'boolean',
        'can_approve' => 'boolean',
        'can_publish' => 'boolean',
    ];
}
