<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivitySchedulingTimeSlot extends Model
{
    use HasFactory;

    protected $table = 'activity_scheduling_timeslots';
    protected $primaryKey = 'timeslot_id';
    public $timestamps = true;

    protected $fillable = [
        'service_id',
        'activity_id',
        'variant_id',
        'service_name',
        'variant_name',
        'participant_equipment_id',
        'capacity_per_slot',
        'schedule_type',
        'start_time',
        'end_time',
        'duration',
        'recurring',
        'lead_time_minutes',
        'days_of_week',
        'discount_value',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'days_of_week' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship to Activity
     */
    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    /**
     * Relationship to Service
     */
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    /**
     * Relationship to ActivityVariant
     */
    public function variant()
    {
        return $this->belongsTo(ActivityVariant::class, 'variant_id');
    }
}
