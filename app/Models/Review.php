<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['trip_id', 'traveler_account_id', 'overall_rating', 'overall_review'];

    public function items()
    {
        return $this->hasMany(ReviewItem::class);
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }
}
