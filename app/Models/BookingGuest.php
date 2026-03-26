<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingGuest extends Model
{
    protected $fillable = [
        'booking_id',
        'booking_type',
        'guest_number',
        'relation',
        'first_name',
        'middle_name',
        'last_name',
        'dob',
        'gender',
        'nationality',
        'passport_number',
        'notes',
    ];
}
