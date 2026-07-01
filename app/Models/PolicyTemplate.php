<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PolicyTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'service_type',
        'policy_type',
        'content',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
