<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MpoPayoutConfigurationEvent extends Model
{
    protected $guarded = [];

    protected $casts = [
        'changes' => 'array',
    ];

    public function configuration()
    {
        return $this->belongsTo(MpoPayoutConfiguration::class, 'payout_configuration_id');
    }
}
