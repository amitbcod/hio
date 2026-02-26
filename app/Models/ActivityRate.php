<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityRate extends Model
{
    protected $table = 'activity_rates';
    protected $primaryKey = 'rate_id';
    protected $guarded = [];
    public $timestamps = true;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $casts = [
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    /**
     * Relationships
     */
    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    public function variant()
    {
        return $this->belongsTo(ActivityVariant::class, 'variant_id', 'variant_id');
    }
}
