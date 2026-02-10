<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccommodationRoom extends Model
{
    protected $table = 'accommodation_rooms';
    protected $guarded = [];
    public $timestamps = true;
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    
    const TYPES = [
        'Single',
        'Double',
        'Twin',
        'Suite',
        'Deluxe',
        'Family',
        'Studio',
        'Bungalow',
        'Villa',
        'Other'
    ];
    
    public function accommodation()
    {
        return $this->belongsTo(Accommodation::class, 'accommodation_id');
    }
    
    public function inventory()
    {
        return $this->hasMany(AccommodationInventory::class, 'room_id');
    }
}
