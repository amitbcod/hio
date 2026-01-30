<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatorProfile extends Model
{
    protected $table = 'operator_profiles';
    protected $guarded = [];
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    // country_of_operation field removed from this model as per new requirements
}
