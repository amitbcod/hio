<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityPolicy extends Model
{
    use HasFactory;

    protected $table = 'activity_policies';
    protected $primaryKey = 'policy_id';

    protected $fillable = [
        'activity_id',
        'service_id',
        'booking_window_rules',
        'booking_window_rules_fr',
        'no_show_policy',
        'no_show_policy_fr',
        'amendment_policy',
        'amendment_policy_fr',
        'amendment_policy_type',
        'amendment_policy_template_id',
        'cancellation_policy',
        'cancellation_policy_fr',
        'cancellation_policy_type',
        'cancellation_policy_template_id',
        'cancellation_penalties_enabled',
        'cancellation_penalties_type',
        'cancellation_penalties_value',
        'child_policy_age',
        'infant_policy_age',
        'safety_requirements',
        'safety_requirements_fr',
        'health_requirements_type',
        'health_requirements_file',
    ];

    protected $casts = [
        'child_policy_age' => 'integer',
        'infant_policy_age' => 'integer',
        'cancellation_penalties_value' => 'decimal:2',
    ];

    /**
     * Get the activity that owns this policy.
     */
    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }
}
