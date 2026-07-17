<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatorDriver extends Model
{
    protected $table = 'operator_drivers';
    protected $guarded = [];
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    // Status constants (matching sheet values)
    const STATUS_ACTIVE = 'Active';
    const STATUS_OFF_DUTY = 'Off Duty';
    const STATUS_SICK_LEAVE = 'Sick Leave';
    const STATUS_SUSPENDED = 'Suspended';
    const STATUS_INACTIVE = 'Inactive';

    protected $casts = [
        'license_expiry_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Operator relationship
     */
    public function operator()
    {
        return $this->belongsTo(Operator::class, 'operator_id', 'operator_id');
    }

    /**
     * Business relationship (if operator is linked to business)
     */
    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'id');
    }

    /**
     * Transport bookings relationship - bookings assigned to this driver
     */
    public function transportBookings()
    {
        return $this->hasMany(TransportBooking::class, 'driver_id', 'id');
    }

    /**
     * Scope: Active drivers only
     */
    public function scopeActive($query)
    {
        return $query->where('driver_status', self::STATUS_ACTIVE);
    }

    /**
     * Scope: Filter by operator
     */
    public function scopeByOperator($query, $operatorId)
    {
        return $query->where('operator_id', $operatorId);
    }

    /**
     * Scope: Filter by business
     */
    public function scopeByBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    /**
     * Get driver phone from mobile or fallback fields
     */
    public function getDriverPhoneAttribute()
    {
        return $this->driver_mobile_no ?? $this->phone ?? null;
    }

    /**
     * Check if driver's documents are valid
     */
    public function hasValidDocuments()
    {
        // Document checks (license expiry) — insurance/background checks are stored on vehicle or other tables.
        $today = now()->toDateString();
        return (!$this->license_expiry_date || $this->license_expiry_date >= $today);
    }

    /**
     * Get document expiry warnings
     */
    public function getDocumentExpiryWarnings()
    {
        $warnings = [];
        $today = now();
        $thirtyDaysFromNow = now()->addDays(30);

        if ($this->license_expiry_date && $this->license_expiry_date <= $thirtyDaysFromNow && $this->license_expiry_date >= $today) {
            $warnings[] = 'Driver License expires on ' . $this->license_expiry_date->format('M d, Y');
        }

        return $warnings;
    }

    /**
     * Generate unique driver ID
     */
    public static function generateDriverId()
    {
        do {
            $driverId = 'DRV' . strtoupper(substr(uniqid(), -8));
        } while (self::where('driver_id', $driverId)->exists());

        return $driverId;
    }
}
