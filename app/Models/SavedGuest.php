<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedGuest extends Model
{
    protected $table = 'saved_guests';
    protected $fillable = [
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'dob',
        'gender',
        'nationality',
        'passport_number',
        'notes',
    ];

    protected $casts = [
        'dob' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(TravelerAccount::class, 'user_id');
    }
}
