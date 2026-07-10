<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportRoute extends Model
{
    protected $table = 'transport_routes';
    protected $guarded = [];

    protected $casts = [
        'pricing' => 'array',
        'is_active' => 'boolean',
    ];

    public function transport()
    {
        return $this->belongsTo(Transport::class, 'transport_id');
    }
}
