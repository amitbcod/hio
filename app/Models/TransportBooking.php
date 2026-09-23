<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportBooking extends Model
{
    public const STATUS_PROCESSING = 'Processing';
    public const STATUS_CONFIRMED = 'Confirmed';
    public const STATUS_SCHEDULED = 'Scheduled';
    public const STATUS_CANCELLED = 'Cancelled';
    public const STATUS_COMPLETED = 'Completed';

    public const STATUSES = [
        self::STATUS_PROCESSING,
        self::STATUS_CONFIRMED,
        self::STATUS_SCHEDULED,
        self::STATUS_CANCELLED,
        self::STATUS_COMPLETED,
    ];

    protected $table = 'transport_bookings';
    protected $fillable = [
        'transport_id',
        'transport_vehicle_id',
        'other_vehicle_name',
        'other_vehicle_license_number',
        'driver_id',
        'pickup_driver_id',
        'return_driver_id',
        'traveler_account_id',
        'booking_reference',
        'guest_name',
        'guest_email',
        'guest_phone',
        'route_from',
        'route_to',
        'pickup_date',
        'pickup_time',
        'return_date',
        'return_time',
        'adults',
        'children',
        'total_passengers',
        'traveler_first_name',
        'traveler_middle_name',
        'traveler_last_name',
        'traveler_relation',
        'traveler_dob',
        'traveler_gender',
        'traveler_nationality',
        'traveler_passport_number',
        'traveler_notes',
        'pickup_address',
        'dropoff_address',
        'service_type',
        'price_per_person',
        'total_amount',
        'currency',
        'booking_status',
        'payment_method',
        'source_channel',
        'special_requests',
        'guest_otp_token_id',
        'is_guest',
        'trip_id',
        'booked_at',
    ];

    protected $casts = [
        'pickup_date' => 'date',
        'return_date' => 'date',
        'traveler_dob' => 'date',
        'price_per_person' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'is_guest' => 'boolean',
        'booked_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $booking): void {
            $booking->booking_status ??= self::STATUS_PROCESSING;
        });
    }

    public static function canTransition(?string $from, string $to): bool
    {
        return in_array($to, match ($from) {
            self::STATUS_PROCESSING => [self::STATUS_CONFIRMED, self::STATUS_CANCELLED],
            self::STATUS_CONFIRMED => [self::STATUS_SCHEDULED, self::STATUS_CANCELLED],
            self::STATUS_SCHEDULED => [self::STATUS_COMPLETED],
            default => [],
        }, true);
    }

    public function hasDriverAssignment(): bool
    {
        return !empty($this->pickup_driver_id ?? $this->driver_id);
    }

    public function hasVehicleAssignment(): bool
    {
        return !empty($this->transport_vehicle_id)
            || (filled($this->other_vehicle_name) && filled($this->other_vehicle_license_number));
    }

    public function hasCompleteAssignment(): bool
    {
        return $this->hasDriverAssignment() && $this->hasVehicleAssignment();
    }

    public function transport()
    {
        return $this->belongsTo(Transport::class, 'transport_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(TransportVehicle::class, 'transport_vehicle_id');
    }

    public function driver()
    {
        return $this->belongsTo(OperatorDriver::class, 'driver_id');
    }

    public function pickupDriver()
    {
        return $this->belongsTo(OperatorDriver::class, 'pickup_driver_id');
    }

    public function returnDriver()
    {
        return $this->belongsTo(OperatorDriver::class, 'return_driver_id');
    }

    // Many-to-many relationship with multiple drivers
    public function drivers()
    {
        return $this->belongsToMany(
            OperatorDriver::class,
            'transport_booking_drivers',
            'transport_booking_id',
            'operator_driver_id'
        )->withTimestamps();
    }

    public function travelerAccount()
    {
        return $this->belongsTo(TravelerAccount::class, 'traveler_account_id');
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class, 'trip_id');
    }

    public function guests()
    {
        return $this->hasMany(BookingGuest::class, 'booking_id')->where('booking_type', 'transport');
    }

    public function guestOtpToken()
    {
        return $this->belongsTo(GuestOtpToken::class, 'guest_otp_token_id');
    }

    public function assignments()
    {
        return $this->hasMany(TransportBookingAssignment::class, 'transport_booking_id');
    }

    public function currentAssignment()
    {
        return $this->hasOne(TransportBookingAssignment::class, 'transport_booking_id')
            ->where('status', TransportBookingAssignment::STATUS_CURRENT);
    }
}
