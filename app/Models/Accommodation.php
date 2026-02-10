<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Accommodation Model
 * 
 * Represents a property/accommodation entity at the property level.
 * 
 * KEY DESIGN RULES:
 * - One Operator can have MANY Accommodations
 * - Each Accommodation may have a different legal holder
 * - Each Accommodation has independent setup/compliance flow
 * - Accommodations are regulated (tourism, safety, insurance, etc.)
 * - Publishing is a state change, not just a flag
 * - Operator account must be active + Controller accepted agreements BEFORE setup can progress
 */
class Accommodation extends Model
{
    use SoftDeletes;
    
    protected $table = 'accommodations';
    protected $guarded = [];
    public $timestamps = true;
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';
    
    // Status constants
    const STATUS_DRAFT = 'Draft';
    const STATUS_IN_SETUP = 'In Setup';
    const STATUS_PENDING_APPROVAL = 'Pending Approval';
    const STATUS_ACTIVE = 'Active';
    const STATUS_SUSPENDED = 'Suspended';
    const STATUS_ARCHIVED = 'Archived';
    
    // Compliance constants
    const COMPLIANCE_NOT_STARTED = 'Not Started';
    const COMPLIANCE_SUBMITTED = 'Submitted';
    const COMPLIANCE_VERIFIED = 'Verified';
    const COMPLIANCE_REJECTED = 'Rejected';
    
    // Property type constants
    const TYPES = [
        'Hotel',
        'Lodge',
        'Guesthouse',
        'Apartment',
        'Holiday Rental',
        'Villa',
        'Resort',
        'Cottage',
        'Other'
    ];
    
    /**
     * Relationships
     */
    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id');
    }
    
    public function operator()
    {
        return $this->belongsTo(Operator::class, 'operator_id');
    }
    
    public function rooms()
    {
        return $this->hasMany(AccommodationRoom::class, 'accommodation_id');
    }
    
    public function compliance()
    {
        return $this->hasOne(AccommodationCompliance::class, 'accommodation_id');
    }
    
    public function inventory()
    {
        return $this->hasMany(AccommodationInventory::class, 'accommodation_id');
    }
    
    public function rates()
    {
        return $this->hasMany(AccommodationRate::class, 'accommodation_id');
    }

    public function media()
    {
        return $this->hasMany(AccommodationMedia::class, 'accommodation_id');
    }
    
    /**
     * Generate unique accommodation ID
     */
    public static function generateAccommodationId()
    {
        do {
            $id = 'ACC' . strtoupper(substr(uniqid(), -8));
        } while (self::where('accommodation_id', $id)->exists());
        
        return $id;
    }
    
    /**
     * Check if accommodation can be published
     * Essential steps must be complete:
     * - Property basics (step 1)
     * - Reservation rules (step 2)
     * - Media (step 3)
     * - Rooms (step 4)
     * - Rates (step 5)
     * - Policies (step 6)
     * - Compliance submitted (step 7)
     */
    public function canPublish()
    {
        // Check operator account status
        if (!$this->operator || $this->operator->account_status !== 'active') {
            return false;
        }
        
        // Check all essential setup steps
        $essentialSteps = [
            'step1_basics' => 1,
            'step2_legal' => 1,
            'step3_media' => 1,
            'step4_rooms' => 1,
            'step5_rates' => 1,
            'step6_policies' => 1,
            'step7_compliance' => 1,
        ];
        
        foreach ($essentialSteps as $step => $required) {
            if ($this->{$step} < $required) {
                return false;
            }
        }
        
        // Check compliance submitted
        if ($this->compliance_documents_submitted === false) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get completion percentage
     */
    public function getCompletionPercentage()
    {
        $steps = [
            'step1_basics',
            'step2_legal',
            'step3_media',
            'step4_rooms',
            'step5_rates',
            'step6_policies',
            'step7_compliance',
            'step8_communication',
            'step9_availability',
            'step10_banking',
            'step11_agents',
            'step12_review',
        ];
        
        $completed = 0;
        foreach ($steps as $step) {
            if ($this->{$step} === 1) {
                $completed++;
            }
        }
        
        return round(($completed / count($steps)) * 100);
    }
    
    /**
     * Mark a step as complete
     */
    public function completeStep($stepName)
    {
        $this->{$stepName} = 1;
        
        // Auto-transition status
        if ($this->status === self::STATUS_DRAFT && $this->{$stepName} === 1) {
            $this->status = self::STATUS_IN_SETUP;
        }
        
        $this->save();
    }
    
    /**
     * Publish the accommodation
     */
    public function publish()
    {
        if (!$this->canPublish()) {
            throw new \Exception('Accommodation cannot be published. Ensure all essential steps are complete.');
        }
        
        $this->is_published = true;
        $this->published_at = now();
        $this->status = self::STATUS_ACTIVE;
        $this->is_visible_to_travellers = true;
        $this->save();
    }
}
