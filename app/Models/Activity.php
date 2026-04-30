<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use SoftDeletes;

    protected $table = 'activities';
    protected $guarded = [];
    public $timestamps = true;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    // Status constants
    const STATUS_DRAFT = 'Draft';
    const STATUS_IN_REVIEW = 'In Review';
    const STATUS_ACTIVE = 'Active';
    const STATUS_INACTIVE = 'Inactive';

    // Service type constants
    const SERVICE_TYPES = ['Activity', 'Tour', 'Park', 'Place of Interest', 'Rental'];

    // Physical level constants
    const PHYSICAL_LEVELS = ['Easy', 'Moderate', 'Challenging'];

    // Price range constants
    const PRICE_RANGES = ['$', '$$', '$$$'];

    // Team categories
    const TEAM_CATEGORIES = ['Family', 'Romantic', 'Eco', 'Corporate', 'Sport', 'Adventure'];

    // Primary themes
    const PRIMARY_THEMES = ['Ocean', 'Culture', 'Nature', 'Adventure', 'Group'];

    // Booking confirmation types
    const BOOKING_CONFIRMATION_TYPES = ['Instant', 'On Request'];

    protected $casts = [
        'team_categories' => 'array',
        'primary_themes' => 'array',
        'languages_offered' => 'array',
        'gallery_images' => 'array',
        'vehicle_images' => 'array',
        'add_ons_available' => 'boolean',
        'private_exclusive_option' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'submitted_for_approval_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function operator()
    {
        return $this->belongsTo(Operator::class, 'operator_id');
    }

    public function compliance()
    {
        return $this->hasOne(ActivityCompliance::class, 'activity_id');
    }

    public function accounting()
    {
        return $this->hasOne(ActivityAccounting::class, 'activity_id');
    }

    public function policy()
    {
        return $this->hasOne(ActivityPolicy::class, 'activity_id');
    }

    public function variants()
    {
        return $this->hasMany(ActivityVariant::class, 'activity_id');
    }

    public function operationsStaffing()
    {
        return $this->hasOne(ActivityOperationsStaffing::class, 'activity_id');
    }

    public function schedulingTimeSlots()
    {
        return $this->hasMany(ActivitySchedulingTimeSlot::class, 'activity_id');
    }

    public function rates()
    {
        return $this->hasMany(ActivityRate::class, 'activity_id');
    }

    public function allotments()
    {
        return $this->hasMany(ActivityAllotment::class, 'activity_id');
    }

    public function blackoutDates()
    {
        return $this->hasMany(ActivityBlackoutDate::class, 'activity_id');
    }

    public function promotions()
    {
        return $this->hasMany(ActivityPromotion::class, 'activity_id');
    }

    public function seoSocial()
    {
        return $this->hasOne(ActivitySeoSocial::class, 'activity_id');
    }

    /**
     * Generate unique service ID
     */
    public static function generateServiceId()
    {
        do {
            $id = 'SVC' . strtoupper(substr(uniqid(), -8));
        } while (self::where('service_id', $id)->exists());

        return $id;
    }

    /**
     * Mark a step as complete
     */
    public function completeStep($stepName)
    {
        $updateData = [$stepName => 1];

        // Auto-transition status
        if ($this->status === self::STATUS_DRAFT) {
            $updateData['status'] = self::STATUS_IN_REVIEW;
        }

        $this->update($updateData);
        $this->refresh();
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeByOperator($query, $operatorId)
    {
        return $query->where('operator_id', $operatorId);
    }
}
