<?php

namespace App\Services;

use App\Models\MpoStaffUser;

class MpoAccountingService
{
    public function eligibleAccountants(int $mpoId): array
    {
        return MpoStaffUser::query()
            ->where('admin_user_id', $mpoId)
            ->where('status', 'Active')
            ->orderBy('full_name')
            ->get()
            ->filter(fn (MpoStaffUser $staff): bool => app(MpoStaffPermissionService::class)->can($staff, 'accounting', 'read'))
            ->values()
            ->all();
    }

    public function validIban(string $iban): bool
    {
        $iban = strtoupper(preg_replace('/\s+/', '', $iban) ?? '');
        if (! preg_match('/^[A-Z]{2}\d{2}[A-Z0-9]{11,30}$/', $iban) || strlen($iban) < 15 || strlen($iban) > 34) {
            return false;
        }

        $rearranged = substr($iban, 4).substr($iban, 0, 4);
        $remainder = 0;
        foreach (str_split($rearranged) as $character) {
            $digits = ctype_alpha($character) ? (string) (ord($character) - 55) : $character;
            foreach (str_split($digits) as $digit) {
                $remainder = (($remainder * 10) + (int) $digit) % 97;
            }
        }

        return $remainder === 1;
    }

    public function isComplete(?string $vatNumber, bool $vatExempted, ?string $vatPercentage, ?array $bankDetails): bool
    {
        if ($vatPercentage === null || (! $vatExempted && ! filled($vatNumber)) || ! is_array($bankDetails)) {
            return false;
        }

        foreach (['account_holder_name', 'iban', 'bank_name', 'swift_bic'] as $requiredField) {
            if (! filled($bankDetails[$requiredField] ?? null)) {
                return false;
            }
        }

        return $this->validIban((string) $bankDetails['iban'])
            && preg_match('/^[A-Z]{4}[A-Z]{2}[A-Z0-9]{2}([A-Z0-9]{3})?$/', strtoupper((string) $bankDetails['swift_bic'])) === 1;
    }

    public function maskedIban(?string $iban): ?string
    {
        if (! $iban) {
            return null;
        }

        $normalized = strtoupper(preg_replace('/\s+/', '', $iban) ?? '');

        return str_repeat('*', max(strlen($normalized) - 4, 0)).substr($normalized, -4);
    }
}
