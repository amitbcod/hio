<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityAllotment extends Model
{
    protected $table = 'activity_allotments';
    protected $primaryKey = 'allotment_id';

    protected $fillable = [
        'service_id',
        'activity_id',
        'service_name',
        'variant_id',
        'variant_name',
        'participant_equipment_id',
        'allotment_strategy',
        'slot_times',
        'inventory_date',
        'allotment',
        'calendar_enabled',
        'calendar_start',
        'calendar_end',
        'season',
    ];

    protected $casts = [
        'slot_times' => 'array',
        'calendar_enabled' => 'boolean',
        'inventory_date' => 'date',
        'calendar_start' => 'date',
        'calendar_end' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    public function variant()
    {
        return $this->belongsTo(ActivityVariant::class, 'variant_id', 'variant_id');
    }
}
