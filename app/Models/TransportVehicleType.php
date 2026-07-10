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

    public function getLabelAttribute()
    {
        return $this->seat_capacity
            ? $this->name . ' (' . $this->seat_capacity . ' Seats)'
            : $this->name;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function activeList(): array
    {
        return self::active()
            ->orderBy('seat_capacity', 'desc')
            ->orderBy('name')
            ->get()
            ->pluck('label', 'name')
            ->toArray();
    }
}
