<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityCompliance extends Model
{
    protected $table = 'activity_compliance';
    protected $guarded = [];
    public $timestamps = true;
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    
    protected $casts = [
        'permits_authorisations_files' => 'array',
        'other_permit_files' => 'array',
        'insurance_expiration' => 'date',
        'verified_at' => 'datetime',
        'is_verified' => 'boolean',
    ];
    
    /**
     * Generate unique compliance ID
     */
    public static function generateComplianceId()
    {
        do {
            $id = 'COMP' . strtoupper(substr(uniqid(), -8));
        } while (self::where('compliance_id', $id)->exists());
        
        return $id;
    }
    
    /**
     * Relationship to Activity
     */
    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }
}
