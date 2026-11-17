<?php

namespace Tests\Unit;

use App\Models\OperatorCollaborationAgreement;
use App\Services\OperatorCommissionCalculator;
use App\Support\FinancialAmount;
use PHPUnit\Framework\TestCase;

class OperatorCommissionCalculatorTest extends TestCase
{
    public function test_step_five_agreement_percentage_is_used_for_operator_commission(): void
    {
        $calculator = new OperatorCommissionCalculator();
        $firstAgreement = new OperatorCollaborationAgreement([
            'agreement_type' => 'OTO',
            'commission_model' => '0',
            'commission_value' => '10.00',
        ]);
        $secondAgreement = new OperatorCollaborationAgreement([
            'agreement_type' => 'Full Service',
            'commission_model' => '0',
            'commission_value' => '25.00',
        ]);

        $this->assertSame([
            'type' => 'percentage',
            'value' => '10.00',
            'amount' => '115.00',
        ], $calculator->calculate($firstAgreement, '1150.00'));
        $this->assertSame([
            'type' => 'percentage',
            'value' => '25.00',
            'amount' => '287.50',
        ], $calculator->calculate($secondAgreement, '1150.00'));
    }

    public function test_zero_commission_agreement_is_valid_and_missing_or_invalid_values_are_rejected(): void
    {
        $calculator = new OperatorCommissionCalculator();
        $zeroAgreement = new OperatorCollaborationAgreement([
            'agreement_type' => 'Listing Only',
            'commission_value' => '0.00',
        ]);
        $invalidAgreement = new OperatorCollaborationAgreement([
            'agreement_type' => 'OTO',
            'commission_value' => '101.00',
        ]);
        $fixedAgreement = new OperatorCollaborationAgreement([
            'agreement_type' => 'OTO',
            'commission_model' => 'Fixed',
            'commission_value' => '10.00',
        ]);

        $this->assertSame('0.00', $calculator->calculate($zeroAgreement, '1150.00')['amount']);
        $this->assertNull($calculator->calculate(null, '1150.00'));
        $this->assertNull($calculator->calculate($invalidAgreement, '1150.00'));
        $this->assertNull($calculator->calculate($fixedAgreement, '1150.00'));
    }

    public function test_example_splits_operator_commission_hio_sla_and_net_payable(): void
    {
        $commission = (new OperatorCommissionCalculator())->calculate(
            new OperatorCollaborationAgreement(['commission_value' => '10.00']),
            '1150.00',
        );
        $hioSla = FinancialAmount::percentage('1150.00', '0.5000');

        $this->assertNotNull($commission);
        $this->assertSame('115.00', $commission['amount']);
        $this->assertSame('5.75', $hioSla);
        $this->assertSame(
            '1029.25',
            FinancialAmount::decimal(
                FinancialAmount::cents('1150.00')
                - FinancialAmount::cents($commission['amount'])
                - FinancialAmount::cents($hioSla),
            ),
        );
    }
}
