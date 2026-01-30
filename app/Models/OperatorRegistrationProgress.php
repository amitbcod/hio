<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatorRegistrationProgress extends Model
{
    protected $table = 'operator_registration_progress';
    protected $guarded = [];
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    // country_of_operation field is now part of this model as per new requirements
}
