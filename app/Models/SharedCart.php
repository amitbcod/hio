<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

class SharedCart extends Model
{
    protected $fillable = [
        'owner_type',
        'owner_id',
        'title',
        'token',
        'items',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'items' => 'array',
        'expires_at' => 'datetime',
    ];

    public function isActive(): bool
    {
        if ($this->status !== 'Active') {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }
}
