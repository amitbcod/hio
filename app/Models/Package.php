<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopesToAdminCountry;

class Package extends Model
{
    use HasFactory;
    use ScopesToAdminCountry;

    protected $fillable = [
        'name',
        'no_of_days',
        'no_of_nights',
        'booking_cutoff_days',
        'available_from',
        'available_to',
        'minimum_pax',
        'maximum_pax',
        'created_by',
        'status',
        'itinerary',
        'country_id',
    ];

    protected $casts = [
        'available_from' => 'date',
        'available_to' => 'date',
        'itinerary' => 'array',
    ];
}
