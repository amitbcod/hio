<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopesToAdminCountry;

class Region extends Model
{
    use ScopesToAdminCountry;

    protected $fillable = ['name'];
}
