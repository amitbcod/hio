<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityBooking extends Model
{
    protected $table = 'activity_bookings';
    protected $guarded = [];
    public $timestamps = true;

    protected $casts = [
        'activity_date' => 'date',
        'booked_at'     => 'datetime',
        'total_amount'  => 'float',
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }
}
