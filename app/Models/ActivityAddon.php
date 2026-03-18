<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityAddon extends Model
{
    protected $table = 'activity_addons';
    protected $primaryKey = 'addon_id';

    protected $fillable = [
        'service_id',
        'activity_id',
        'addon_name',
        'pricing_type',
        'price',
        'addon_type',
        'variant_id',
        'variant_name',
        'availability_rules',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    /**
     * Relationship to Activity
     */
    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    /**
     * Relationship to ActivityVariant
     */
    public function variant()
    {
        return $this->belongsTo(ActivityVariant::class, 'variant_id', 'variant_id');
    }
}
