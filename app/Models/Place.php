<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopesToAdminCountry;

class Place extends Model
{
    use ScopesToAdminCountry;

    protected $table = 'places';
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
