<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingWidget extends Model
{
    use HasFactory;

    protected $fillable = [
        'operator_id',
        'widget_token',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
