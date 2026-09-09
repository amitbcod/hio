<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $table = 'groups';

    protected $fillable = [
        'name',
        'group_type',
        'closed_group_client',
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
    ];

    protected $casts = [
        'available_from' => 'date',
        'available_to' => 'date',
        'itinerary' => 'array',
    ];
}
