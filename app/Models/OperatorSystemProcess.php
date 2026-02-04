<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatorSystemProcess extends Model
{
    protected $table = 'operator_system_processes';
    protected $guarded = [];
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    // Business relationship: prefer business-scoped process when available
    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id');
    }
}