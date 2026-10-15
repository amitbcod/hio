<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function adminUsers()
    {
        return $this->hasMany(AdminUser::class);
    }

    public function businesses()
    {
        return $this->hasMany(Business::class);
    }
}
