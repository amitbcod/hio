<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccommodationRate extends Model
{
    protected $table = 'accommodation_rates';
    protected $guarded = [];
    public $timestamps = true;
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    
    const MEAL_PLANS = [
        'Room Only',
        'Breakfast',
        'Half Board',
        'Full Board',
        'All Inclusive'
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
