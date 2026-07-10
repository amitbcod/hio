<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transport extends Model
{
    use SoftDeletes;

    protected $table = 'transports';
    protected $guarded = [];
    protected $attributes = [
        'approval_status' => 'Draft',
    ];
    public $timestamps = true;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    // Status constants
    const STATUS_DRAFT = 'Draft';
    const STATUS_IN_REVIEW = 'In Review';
    const STATUS_ACTIVE = 'Active';
    const STATUS_INACTIVE = 'Inactive';
    const STATUS_ARCHIVED = 'Archived';

    // Vehicle types
    const VEHICLE_TYPES = ['Car', 'Van', 'SUV', 'Sedan', 'Bus', 'Minibus', 'Taxi', 'Limousine', 'Other'];

    protected $casts = [
        'routes_pricing' => 'array',
        'promotions_offers' => 'array',
        'amenities' => 'array',
        'amenities_fr' => 'array',
        'gallery_images' => 'array',
        'insurance_expiration' => 'date',
        'license_expiration' => 'date',
        'submitted_for_approval_at' => 'datetime',
        'approved_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function operator()
    {
        return $this->belongsTo(Operator::class, 'operator_id');
    }

    public function rates()
    {
        return $this->hasMany(TransportRate::class, 'transport_id');
    }

    public function routes()
    {
        return $this->hasMany(TransportRoute::class, 'transport_id');
    }

    public function bookings()
    {
        return $this->hasMany(TransportBooking::class, 'transport_id');
    }

    /**
     * Generate unique service ID
     */
    public static function generateServiceId()
    {
        do {
            $id = 'TRN' . strtoupper(substr(uniqid(), -8));
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

    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'Approved');
    }

    public function scopeApprovedForFrontend($query)
    {
        return $query->where('approval_status', 'Approved')
            ->where('is_published', true)
            ->where('is_visible_to_travellers', true)
            ->where('status', self::STATUS_ACTIVE);
    }
}
