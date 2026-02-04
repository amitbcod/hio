<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Business;

class OperatorProfile extends Model
{
    protected $table = 'operator_profiles';
    protected $guarded = [];
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    // The canonical reference for business-scoped profile
    protected $casts = [
        'service_types' => 'array',
        'contact_details' => 'array',
        'social_media_links' => 'array',
    ];

    // Relations
    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    // country_of_operation field removed from this model as per new requirements
}
