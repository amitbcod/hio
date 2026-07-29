<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportServiceRoutePair extends Model
{
    protected $table = 'transport_service_route_pairs';

    protected $fillable = [
        'service_type',
        'route_from',
        'route_to',
        'is_active',
        'trip_time_minutes',
        'buffer_time_minutes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
