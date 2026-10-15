<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopesToAdminCountry;

class BookingGuest extends Model
{
    use ScopesToAdminCountry;

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

    protected $casts = [
        'dob' => 'date',
    ];
}
