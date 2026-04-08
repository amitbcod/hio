<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BliTravellerAllocation extends Model
{
    protected $fillable = ['bli_id', 'traveller_id'];

    public function bli()
    {
        return $this->belongsTo(BookingLineItem::class, 'bli_id');
    }

    public function traveller()
    {
        return $this->belongsTo(Traveller::class, 'traveller_id');
    }
}
