<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityVariant extends Model
{
    use HasFactory;

    protected $table = 'activity_variants';
    protected $primaryKey = 'variant_id';

    protected $fillable = [
        'activity_id',
        'service_id',
        'variant_equipment_id',
        'variant_name',
        'variant_name_fr',
        'quality_tier',
        'amenities',
        'safety_equipment',
        'max_pax',
        'min_participants',
        'max_participants',
        'allotment',
        'private_exclusive',
        'equipment_image',
    ];

    protected $casts = [
        'amenities' => 'array',
        'safety_equipment' => 'array',
        'max_pax' => 'integer',
        'min_participants' => 'integer',
        'max_participants' => 'integer',
    ];

    /**
     * Get the activity that owns this variant.
     */
    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    /**
     * Generate unique variant equipment ID
     */
    public static function generateVariantEquipmentId($activityId)
    {
        $activity = Activity::find($activityId);
        if (!$activity) {
            return null;
        }

        // Format: SERVICE_ID + variant counter
        $count = self::where('activity_id', $activityId)->count();
        $variantCode = $count + 1;

        return $activity->service_id . '-VAR-' . str_pad($variantCode, 3, '0', STR_PAD_LEFT);
    }
}
