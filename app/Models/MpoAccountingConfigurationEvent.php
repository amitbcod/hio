<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MpoAccountingConfigurationEvent extends Model
{
    protected $guarded = [];

    protected $casts = [
        'changes' => 'array',
    ];

    public function mpo()
    {
        return $this->belongsTo(AdminUser::class, 'admin_user_id');
    }
}
