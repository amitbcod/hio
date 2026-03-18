<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityBlackoutDate extends Model
{
    protected $table = 'activity_blackout_dates';
    protected $primaryKey = 'blackout_id';

    protected $fillable = [
        'activity_id',
        'variant_id',
        'season',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    public function variant()
    {
        return $this->belongsTo(ActivityVariant::class, 'variant_id', 'variant_id');
    }
}
