<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopesToAdminCountry;

class OperatorStatusReview extends Model
{
    use ScopesToAdminCountry;
    protected $table = 'operator_status_review';
    protected $guarded = [];
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    // Business relation
    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    protected $casts = [
        'operator_rating' => 'float',
        'average_rating' => 'float',
    ];
}
