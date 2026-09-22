<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportVehicle extends Model
{
    protected $table = 'transport_vehicles';

    protected $fillable = [
        'transport_id',
        'license_number',
        'registration_number',
        'license_expiry_date',
        'insurance_expiry_date',
        'insurance_provider',
        'policy_path',
        'documents',
        'status',
    ];

    protected $casts = [
        'license_expiry_date' => 'date',
        'insurance_expiry_date' => 'date',
        'documents' => 'array',
    ];

    public const STATUSES = [
        'Active',
        'Maintenance',
        'Breakdown',
        'Suspended',
        'Out of Service',
        'Reserve',
    ];

    public function transport()
    {
        return $this->belongsTo(Transport::class, 'transport_id');
    }

    public function bookings()
    {
        return $this->hasMany(TransportBooking::class, 'transport_vehicle_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }
}