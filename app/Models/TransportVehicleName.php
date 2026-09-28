<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportVehicleName extends Model
{
    protected $table = 'transport_vehicle_names';
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function vehicleType()
    {
        return $this->belongsTo(TransportVehicleType::class, 'transport_vehicle_type_id');
    }

    public function transports()
    {
        return $this->hasMany(Transport::class, 'vehicle_name_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}