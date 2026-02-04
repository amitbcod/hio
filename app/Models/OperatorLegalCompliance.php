<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatorLegalCompliance extends Model
{
    protected $table = 'operator_legal_compliance';
    protected $guarded = [];
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    // Business relation now available
    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    protected $casts = [
        'service_package' => 'string',
    ];
}
