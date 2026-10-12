<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingScheduleChange extends Model
{
    protected $guarded = [];

    protected $casts = [
        'previous_schedule' => 'array',
        'new_schedule' => 'array',
    ];

    public function actor()
    {
        return $this->morphTo();
    }
}
