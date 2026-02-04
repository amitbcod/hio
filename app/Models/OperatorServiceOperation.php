<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Business;

class OperatorServiceOperation extends Model
{
    protected $table = 'operator_service_operations';
    protected $guarded = [];
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    // Relation to business
    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    protected $casts = [
        'operating_areas' => 'array',
    ];
}
