<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MpoCollaborationAgreementEvent extends Model
{
    protected $guarded = [];

    protected $casts = [
        'changes' => 'array',
    ];

    public function agreement()
    {
        return $this->belongsTo(MpoCollaborationAgreement::class, 'agreement_id');
    }
}
