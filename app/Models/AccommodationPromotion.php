<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccommodationPromotion extends Model
{
    protected $table = 'accommodation_promotions';
    protected $guarded = [];
    public $timestamps = true;
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    
    protected $casts = [
        'promo_valid_from' => 'date',
        'promo_valid_to' => 'date',
        'non_refundable' => 'boolean',
        'discount_value' => 'decimal:2',
    ];
    
    // Relationships
    public function accommodation()
    {
        return $this->belongsTo(Accommodation::class, 'accommodation_id');
    }
    
    public function room()
    {
        return $this->belongsTo(AccommodationRoom::class, 'room_id');
    }
    
    public function ratePlan()
    {
        return $this->belongsTo(AccommodationRate::class, 'rate_plan_id');
    }
    
    // Scopes
    public function scopePublished($query)
    {
        return $query->where('approval_status', 'Published');
    }
    
    public function scopeDraft($query)
    {
        return $query->where('approval_status', 'Draft');
    }
    
    public function scopePendingApproval($query)
    {
        return $query->where('approval_status', 'Pending Approval');
    }
    
    public function scopeActive($query)
    {
        return $query->where('approval_status', 'Published')
            ->where(function($q) {
                $q->whereNull('promo_valid_from')
                  ->orWhere('promo_valid_from', '<=', now()->toDateString());
            })
            ->where(function($q) {
                $q->whereNull('promo_valid_to')
                  ->orWhere('promo_valid_to', '>=', now()->toDateString());
            });
    }
}
