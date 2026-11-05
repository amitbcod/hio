<?php

namespace App\Services;

use App\Models\AdminUser;
use App\Models\MpoCollaborationAgreement;

class MpoRegistrationCompletionService
{
    public function state(AdminUser $mpo): array
    {
        $mpo = AdminUser::query()->with('country')->findOrFail($mpo->id);
        $agreement = $mpo->collaborationAgreements()->latest('id')->first();
        $accounting = $mpo->accountingConfiguration()->first();
        $compliance = $mpo->compliance()->first();
        $profileComplete = $mpo->hasCompleteMpoProfile();
        $complianceComplete = $compliance?->verification_status === 'verified';

        $agreementComplete = $agreement instanceof MpoCollaborationAgreement
            && $agreement->status === 'Active'
            && $agreement->isOnboardingComplete()
            && $agreement->start_date !== null
            && ($agreement->start_date->isPast() || $agreement->start_date->isToday());
        $accountingComplete = app(MpoAccountingService::class)->isComplete(
            $accounting?->vat_number,
            (bool) $accounting?->vat_exempted,
            $mpo->country?->vat_percentage,
            $accounting?->bank_details,
        );

        return [
            'complete' => $profileComplete && $complianceComplete && $agreementComplete && $accountingComplete,
            'profile' => $profileComplete,
            'compliance' => $complianceComplete,
            'agreement' => $agreementComplete,
            'accounting' => $accountingComplete,
            'agreementRecord' => $agreement,
            'complianceRecord' => $compliance,
            'accountingRecord' => $accounting,
        ];
    }

    public function nextRoute(AdminUser $mpo): string
    {
        $state = $this->state($mpo);

        if (! $state['profile']) {
            return 'mpo.profile';
        }
        if (! $state['compliance']) {
            return 'mpo.compliances';
        }
        if (! $state['agreement']) {
            return 'mpo.collaboration-agreements';
        }
        if (! $state['accounting']) {
            return 'mpo.accounting-commission';
        }

        return 'mpo.dashboard';
    }
}
