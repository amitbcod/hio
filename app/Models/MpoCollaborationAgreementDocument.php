<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MpoCollaborationAgreementDocument extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_current' => 'boolean',
        'size_bytes' => 'integer',
    ];

    public function agreement()
    {
        return $this->belongsTo(MpoCollaborationAgreement::class, 'agreement_id');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(AdminUser::class, 'uploaded_by_admin_id');
    }
}
