<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MpoCollaborationAgreement extends Model
{
    protected $guarded = [];

    protected $casts = [
        'onboarding_date' => 'date',
        'start_date' => 'date',
        'commission_value' => 'decimal:4',
        'marketing_contribution_percent' => 'decimal:4',
    ];

    public function mpo()
    {
        return $this->belongsTo(AdminUser::class, 'admin_user_id');
    }

    public function documents()
    {
        return $this->hasMany(MpoCollaborationAgreementDocument::class, 'agreement_id')->orderByDesc('id');
    }

    public function currentDocument()
    {
        return $this->hasOne(MpoCollaborationAgreementDocument::class, 'agreement_id')->where('is_current', true)->latestOfMany();
    }

    public function events()
    {
        return $this->hasMany(MpoCollaborationAgreementEvent::class, 'agreement_id')->latest();
    }

    public function isOnboardingComplete(): bool
    {
        return filled($this->agreement_type)
            && $this->onboarding_date !== null
            && $this->start_date !== null
            && $this->commission_model === 'percentage'
            && $this->commission_value !== null
            && filled($this->management_contact_name)
            && filled($this->management_contact_email)
            && filled($this->management_contact_phone)
            && filled($this->management_contact_mobile)
            && filled($this->accounting_contact_name)
            && filled($this->accounting_contact_email)
            && filled($this->accounting_contact_phone)
            && filled($this->accounting_contact_mobile);
    }

    public function accountingCommissionTerms(): array
    {
        return [
            'model' => $this->commission_model,
            'value' => $this->commission_value,
            'status' => $this->status,
            'start_date' => $this->start_date,
        ];
    }
}
