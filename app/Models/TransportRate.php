<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportRate extends Model
{
    protected $table = 'transport_rates';
    protected $fillable = [
        'transport_id',
        'route_from',
        'route_to',
        'price_per_person',
        'price_per_vehicle',
        'min_passengers',
        'max_passengers',
        'valid_from',
        'valid_to',
        'is_active',
    ];

    protected $casts = [
        'price_per_person' => 'decimal:2',
        'price_per_vehicle' => 'decimal:2',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'is_active' => 'boolean',
    ];

    public function transport()
    {
        return $this->belongsTo(Transport::class, 'transport_id');
    }
}
