<?php

namespace App\Services;

use App\Models\AdminUser;
use App\Models\MpoAccountingConfiguration;
use App\Models\MpoCollaborationAgreement;
use App\Models\MpoPayoutConfiguration;

class MpoPayoutConfigurationService
{
    public function completionState(AdminUser $mpo, ?MpoPayoutConfiguration $configuration = null): array
    {
        $mpo->loadMissing(['country', 'accountingConfiguration', 'payoutConfiguration', 'collaborationAgreements']);
        $configuration ??= $mpo->payoutConfiguration;
        $accounting = $mpo->accountingConfiguration;
        $agreement = $this->applicableAgreement($mpo);
        $bank = $accounting?->bank_details ?? [];
        $accountingComplete = app(MpoAccountingService::class)->isComplete(
            $accounting?->vat_number,
            (bool) $accounting?->vat_exempted,
            $mpo->country?->vat_percentage,
            $bank,
        );
        $bankValid = $accountingComplete
            && filled($bank['account_holder_name'] ?? null)
            && filled($bank['bank_name'] ?? null)
            && app(MpoAccountingService::class)->validIban((string) ($bank['iban'] ?? ''))
            && preg_match('/^[A-Z]{4}[A-Z]{2}[A-Z0-9]{2}([A-Z0-9]{3})?$/', strtoupper((string) ($bank['swift_bic'] ?? ''))) === 1;
        $agreementReady = $agreement !== null
            && $agreement->status === 'Active'
            && $agreement->isOnboardingComplete()
            && ($agreement->start_date === null || $agreement->start_date->isPast() || $agreement->start_date->isToday());

        $requirements = [];
        if (! $agreementReady) {
            $requirements[] = 'An active, complete Collaboration Agreement with an effective start date is required.';
        }
        if (! $accountingComplete) {
            $requirements[] = 'Complete Accounting Commission, including the country VAT setting and valid bank details.';
        }
        if (! $configuration?->payment_schedule) {
            $requirements[] = 'Choose a payout schedule.';
        }
        if ($configuration?->payout_method !== 'Bank' || ! $bankValid) {
            $requirements[] = 'Select Bank and ensure valid bank details are configured in Accounting Commission.';
        }

        return [
            'complete' => $requirements === [],
            'requirements' => $requirements,
            'agreement' => $agreement,
            'accountingComplete' => $accountingComplete,
            'bankValid' => $bankValid,
        ];
    }

    public function applicableAgreement(AdminUser $mpo): ?MpoCollaborationAgreement
    {
        return $mpo->collaborationAgreements()->latest('id')->first();
    }

    public function bankSummary(?MpoAccountingConfiguration $accounting): array
    {
        $bank = $accounting?->bank_details ?? [];
        $iban = strtoupper(preg_replace('/\s+/', '', (string) ($bank['iban'] ?? '')) ?? '');

        return [
            'bank_name' => $bank['bank_name'] ?? null,
            'iban' => $iban === '' ? null : '**** '.substr($iban, -4),
            'valid' => filled($bank['account_holder_name'] ?? null)
                && filled($bank['bank_name'] ?? null)
                && app(MpoAccountingService::class)->validIban($iban)
                && preg_match('/^[A-Z]{4}[A-Z]{2}[A-Z0-9]{2}([A-Z0-9]{3})?$/', strtoupper((string) ($bank['swift_bic'] ?? ''))) === 1,
        ];
    }
}
