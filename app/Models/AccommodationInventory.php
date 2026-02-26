<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccommodationInventory extends Model
{
    protected $table = 'accommodation_inventory';
    protected $guarded = [];
    public $timestamps = true;
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    
    protected $casts = [
        'date' => 'date',
        'blackout_dates' => 'json',
    ];
    
    public function accommodation()
    {
        return $this->belongsTo(Accommodation::class, 'accommodation_id');
    }
    
    public function room()
    {
        return $this->belongsTo(AccommodationRoom::class, 'room_id');
    }
}
