<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportVehicleType extends Model
{
    protected $table = 'transport_vehicle_types';
    protected $guarded = [];
    public $timestamps = true;

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function vehicleNames()
    {
        return $this->hasMany(TransportVehicleName::class, 'transport_vehicle_type_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function activeList(): array
    {
        return self::active()
            ->orderBy('name')
            ->pluck('name', 'name')
            ->toArray();
    }
}
