<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MpoPayoutConfiguration extends Model
{
    protected $guarded = [];

    public function mpo()
    {
        return $this->belongsTo(AdminUser::class, 'admin_user_id');
    }

    public function events()
    {
        return $this->hasMany(MpoPayoutConfigurationEvent::class, 'payout_configuration_id')->latest();
    }
}
