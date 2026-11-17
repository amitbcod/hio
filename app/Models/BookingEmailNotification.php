<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingEmailNotification extends Model
{
    protected $guarded = [];

    protected $casts = [
        'service_booking_ids' => 'array',
        'processing_at' => 'datetime',
        'dispatched_at' => 'datetime',
        'failed_at' => 'datetime',
    ];
}
