<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccommodationBooking extends Model
{
    protected $table = 'accommodation_bookings';
    protected $guarded = [];
    public $timestamps = true;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'traveler_dob' => 'date',
        'booked_at' => 'datetime',
    ];

    public function accommodation()
    {
        return $this->belongsTo(Accommodation::class, 'accommodation_id');
    }

    public function room()
    {
        return $this->belongsTo(AccommodationRoom::class, 'room_id');
    }

    public function guests()
    {
        return $this->hasMany(BookingGuest::class, 'booking_id')->where('booking_type', 'accommodation');
    }

    public function guestOtpToken()
    {
        return $this->belongsTo(GuestOtpToken::class, 'guest_otp_token_id');
    }
}
