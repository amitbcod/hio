<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportBooking extends Model
{
    protected $table = 'transport_bookings';
    protected $fillable = [
        'transport_id',
        'driver_id',
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
        'dropoff_address',
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

    public function transport()
    {
        return $this->belongsTo(Transport::class, 'transport_id');
    }

    public function driver()
    {
        return $this->belongsTo(OperatorDriver::class, 'driver_id');
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
}
