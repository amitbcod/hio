<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TravelerProfile extends Model
{
    protected $table = 'traveler_profiles';
    protected $guarded = [];
    public $timestamps = true;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function account()
    {
        return $this->belongsTo(TravelerAccount::class, 'traveler_account_id');
    }
}
