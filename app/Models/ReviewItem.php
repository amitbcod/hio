<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewItem extends Model
{
    protected $fillable = ['review_id', 'service_type', 'service_id', 'criteria', 'review'];

    protected $casts = [
        'criteria' => 'array',
    ];

    public function parentReview()
    {
        return $this->belongsTo(Review::class, 'review_id');
    }
}
