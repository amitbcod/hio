<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatorRegistrationProgress extends Model
{
    protected $table = 'operator_registration_progress';
    protected $guarded = [];
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    // Relation to business (new canonical reference)
    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    // convenience accessor to get a stable scope key for this progress (business preferred)
    public function getScopeKey()
    {
        return $this->business_id ?? $this->operator_id;
    }

    // country_of_operation field is now part of this model as per new requirements
}
