<?php

namespace App\Models;

use App\Support\CountryAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['trip_id', 'traveler_account_id', 'overall_rating', 'overall_review'];

    protected static function booted(): void
    {
        static::addGlobalScope('admin_country', function (Builder $query): void {
            if (CountryAccess::isMpoAdmin()) {
                $query->whereHas('trip');
            }
        });
    }

    public function items()
    {
        return $this->hasMany(ReviewItem::class);
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }
}
