<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportBookingAssignment extends Model
{
    protected $table = 'transport_booking_assignments';

    protected $fillable = [
        'transport_booking_id',
        'vehicle_id',
        'driver_id',
        'other_vehicle_name',
        'other_vehicle_license_number',
        'status',
        'reason',
        'assigned_at',
        'unassigned_at',
        'assigned_by',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'unassigned_at' => 'datetime',
    ];

    public const STATUS_CURRENT = 'Current';
    public const STATUS_REPLACED = 'Replaced';
    public const STATUS_UNASSIGNED = 'Unassigned';

    public function booking()
    {
        return $this->belongsTo(TransportBooking::class, 'transport_booking_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(TransportVehicle::class, 'vehicle_id');
    }

    public function driver()
    {
        return $this->belongsTo(OperatorDriver::class, 'driver_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(Operator::class, 'assigned_by');
    }
}