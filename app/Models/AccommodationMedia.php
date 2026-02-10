<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccommodationMedia extends Model
{
    protected $table = 'accommodation_media';
    protected $guarded = [];

    public function accommodation()
    {
        return $this->belongsTo(Accommodation::class, 'accommodation_id');
    }

    public function room()
    {
        return $this->belongsTo(AccommodationRoom::class, 'room_id');
    }
}
