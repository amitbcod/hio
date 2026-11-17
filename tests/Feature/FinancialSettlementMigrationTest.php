<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class FinancialSettlementMigrationTest extends TestCase
{
    public function test_financial_settlement_tables_migrate_with_short_index_names(): void
    {
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        foreach (['countries', 'admin_users', 'operators', 'booking_refs', 'trips'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table): void {
                $table->id();
            });
        }

        $migration = require database_path('migrations/2026_11_16_000001_create_accounting_settlement_tables.php');
        $migration->up();

        foreach ([
            'financial_settlements',
            'financial_settlement_items',
            'financial_settlement_transfers',
            'financial_settlement_events',
            'financial_booking_reviews',
        ] as $tableName) {
            $this->assertTrue(Schema::hasTable($tableName), "Expected {$tableName} to be created.");
        }

        $invoiceMigration = require database_path('migrations/2026_11_16_000002_add_sla_invoice_fields_to_financial_settlements.php');
        $invoiceMigration->up();

        foreach (['invoice_reference', 'invoice_issued_on', 'invoice_due_on'] as $column) {
            $this->assertTrue(Schema::hasColumn('financial_settlements', $column), "Expected {$column} invoice column.");
        }

        $countryId = DB::table('countries')->insertGetId([]);
        $mpoId = DB::table('admin_users')->insertGetId([]);
        $operatorId = DB::table('operators')->insertGetId([]);
        $operatorSettlementId = DB::table('financial_settlements')->insertGetId([
            'reference' => 'OP-TEST-1',
            'generated_key' => 'operator-test-key',
            'settlement_type' => 'operator_payout',
            'country_id' => $countryId,
            'mpo_admin_user_id' => $mpoId,
            'operator_id' => $operatorId,
            'payment_schedule' => 'Monthly',
            'period_start' => '2026-10-01',
            'period_end' => '2026-10-31',
            'currency' => 'USD',
            'gross_amount' => 100,
            'operator_commission_amount' => 10,
            'hio_sla_amount' => 0.50,
            'net_amount' => 89.50,
            'status' => 'Awaiting Authorisation',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $service = new \App\Services\FinancialSettlementService(new \App\Services\OperatorCommissionCalculator());
        $operatorSettlement = \App\Models\FinancialSettlement::query()->findOrFail($operatorSettlementId);
        try {
            $service->recordTransfer($operatorSettlement, $this->transferData('89.50'), 'mpo', $mpoId);
            $this->fail('A transfer must not be recorded before payout authorization.');
        } catch (HttpException $exception) {
            $this->assertSame(409, $exception->getStatusCode());
        }

        $service->authorizeOperatorPayout($operatorSettlement, 'mpo', $mpoId);
        $service->recordTransfer($operatorSettlement, $this->transferData('40.00'), 'mpo', $mpoId);
        $firstTransferId = (int) DB::table('financial_settlement_transfers')->value('id');
        $service->verifyTransfer($operatorSettlement, $firstTransferId, 'operator', $operatorId);
        $this->assertSame('Partially Paid', $operatorSettlement->fresh()->status);

        $service->recordTransfer($operatorSettlement, $this->transferData('49.50'), 'mpo', $mpoId);
        $secondTransferId = (int) DB::table('financial_settlement_transfers')->orderByDesc('id')->value('id');
        $service->verifyTransfer($operatorSettlement, $secondTransferId, 'operator', $operatorId);
        $this->assertSame('Paid', $operatorSettlement->fresh()->status);

        $hioSettlementId = DB::table('financial_settlements')->insertGetId([
            'reference' => 'HIO-TEST-1',
            'generated_key' => 'hio-test-key',
            'settlement_type' => 'hio_sla',
            'country_id' => $countryId,
            'mpo_admin_user_id' => $mpoId,
            'payment_schedule' => 'Monthly',
            'period_start' => '2026-10-01',
            'period_end' => '2026-10-31',
            'currency' => 'USD',
            'gross_amount' => 1000,
            'operator_commission_amount' => 0,
            'hio_sla_amount' => 5,
            'net_amount' => 5,
            'status' => 'Statement Generated',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $hioSettlement = \App\Models\FinancialSettlement::query()->findOrFail($hioSettlementId);
        $service->issueHioInvoice($hioSettlement, 'HIO-INV-TEST-1', '2026-11-01', '2026-11-30', 'global_admin', 1);
        $this->assertSame('Payable', $hioSettlement->fresh()->status);
        $this->assertSame('HIO-INV-TEST-1', $hioSettlement->fresh()->invoice_reference);
        $service->recordTransfer($hioSettlement, $this->transferData('5.00'), 'mpo', $mpoId);
        $hioTransferId = (int) DB::table('financial_settlement_transfers')->orderByDesc('id')->value('id');
        $service->verifyTransfer($hioSettlement, $hioTransferId, 'global_admin', 1);
        $this->assertSame('Received', $hioSettlement->fresh()->status);
        $service->acknowledgeHioReceipt($hioSettlement, 'global_admin', 1);
        $this->assertSame('Acknowledged', $hioSettlement->fresh()->status);
    }

    private function transferData(string $amount): array
    {
        return [
            'amount' => $amount,
            'transaction_number' => 'TX-'.str_replace('.', '', $amount),
            'transferred_on' => '2026-11-01',
            'evidence_path' => 'test/evidence.pdf',
            'notes' => null,
        ];
    }
}
