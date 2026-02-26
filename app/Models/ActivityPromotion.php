<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityPromotion extends Model
{
    protected $table = 'activity_promotions';
    protected $primaryKey = 'promotion_id';
    public $timestamps = true;

    protected $fillable = [
        'activity_id',
        'service_id',
        'campaign_id',
        'campaign_name',
        'campaign_description',
        'specifications',
        'inclusions',
        'exclusions',
        'discount_type',
        'discount_value',
        'promo_valid_from',
        'promo_valid_to',
        'non_refundable',
        'approval_status',
        'variant_ids',
    ];

    protected $casts = [
        'variant_ids' => 'array',
        'promo_valid_from' => 'date',
        'promo_valid_to' => 'date',
    ];

    /**
     * Relationship to Activity
     */
    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    /**
     * Get variants as a collection
     */
    public function getVariants()
    {
        if (!$this->variant_ids) {
            return collect();
        }
        return ActivityVariant::whereIn('variant_id', $this->variant_ids)->get();
    }

    /**
     * Generate unique campaign ID
     */
    public static function generateCampaignId($activityId)
    {
        $prefix = 'CMP-' . str_pad($activityId, 4, '0', STR_PAD_LEFT) . '-';
        $count = self::where('activity_id', $activityId)->count() + 1;
        return $prefix . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
