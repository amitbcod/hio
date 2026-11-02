<?php

namespace App\Models;

use App\Models\Concerns\ScopesToAdminCountry;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use ScopesToAdminCountry;

    protected $fillable = ['name', 'country_id'];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
