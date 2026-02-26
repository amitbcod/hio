<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityOperationsStaffing extends Model
{
    use HasFactory;

    protected $table = 'activity_operations_staffing';
    protected $primaryKey = 'operation_id';

    protected $fillable = [
        'activity_id',
        'service_id',
        'variant_id',
        'variant_equipment_id',
        'age_groups',
        'pickup_options',
        'dropoff_options',
        'accessibility_features',
        'ops_contact_name',
        'ops_contact_mobile',
        'crew_guide_count',
        'crew_guide_requirements',
        'special_equipment_notes',
    ];

    protected $casts = [
        'age_groups' => 'array',
        'accessibility_features' => 'array',
    ];

    /**
     * Relationship to Activity
     */
    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    /**
     * Relationship to Variant
     */
    public function variant()
    {
        return $this->belongsTo(ActivityVariant::class, 'variant_id');
    }
}
