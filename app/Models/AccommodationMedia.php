<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopesToAdminCountry;

class AccommodationMedia extends Model
{
    use ScopesToAdminCountry;
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
