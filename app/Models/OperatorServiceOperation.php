<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatorServiceOperation extends Model
{
    protected $table = 'operator_service_operations';
    protected $guarded = [];
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
}
