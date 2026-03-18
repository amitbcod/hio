<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccommodationFee extends Model
{
    protected $table = 'accommodation_fees';

    protected $fillable = [
        'accommodation_id',
        'room_id',
        'cleaning_fee',
        'resort_fee',
        'early_checkin_type',
        'early_checkin_value',
        'late_checkout_type',
        'late_checkout_value',
    ];
}
