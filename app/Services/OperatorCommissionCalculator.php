<?php

namespace App\Services;

use App\Models\OperatorCollaborationAgreement;
use App\Support\FinancialAmount;

final class OperatorCommissionCalculator
{
    /**
     * Step 5 stores the commission as a percentage; Step 7 commission settings are payout metadata only.
     *
     * @return array{type: string, value: string, amount: string}|null
     */
    public function calculate(?OperatorCollaborationAgreement $agreement, string $grossAmount): ?array
    {
        if (! $agreement || ! is_numeric($agreement->commission_value)) {
            return null;
        }

        $commissionModel = strtolower(trim((string) $agreement->commission_model));
        if (! in_array($commissionModel, ['', '0', 'percentage'], true)) {
            return null;
        }

        $value = (string) $agreement->commission_value;
        if (! preg_match('/^\d+(?:\.\d{1,2})?$/', $value) || (float) $value > 100) {
            return null;
        }

        return [
            'type' => 'percentage',
            'value' => $value,
            'amount' => FinancialAmount::percentage($grossAmount, $value),
        ];
    }
}
