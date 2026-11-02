<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MpoAccountingConfiguration extends Model
{
    protected $guarded = [];

    protected $casts = [
        'vat_exempted' => 'boolean',
        'bank_details' => 'encrypted:array',
        'bank_details_changed_at' => 'datetime',
    ];

    protected $hidden = [
        'bank_details',
    ];

    public function mpo()
    {
        return $this->belongsTo(AdminUser::class, 'admin_user_id');
    }

    public function responsibleAccountant()
    {
        return $this->belongsTo(MpoStaffUser::class, 'responsible_accountant_user_id');
    }
}
