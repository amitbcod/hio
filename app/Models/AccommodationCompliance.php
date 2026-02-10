<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccommodationCompliance extends Model
{
    protected $table = 'accommodation_compliance';
    protected $guarded = [];
    public $timestamps = true;
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    
    public function accommodation()
    {
        return $this->belongsTo(Accommodation::class, 'accommodation_id');
    }
}
