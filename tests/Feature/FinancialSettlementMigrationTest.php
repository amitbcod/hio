<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Operator;
use App\Notifications\FinancialSettlementTransferNotification;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class FinancialSettlementMigrationTest extends TestCase
{
    public function test_financial_settlement_tables_migrate_with_short_index_names(): void
    {
        Notification::fake();
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        foreach (['countries', 'operators', 'booking_refs', 'trips'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table): void {
                $table->id();
            });
        }
        Schema::create('admin_users', function (Blueprint $table): void {
            $table->id();
            $table->string('admin_type')->default('mpo');
            $table->string('status')->default('active');
        });

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
        $notificationMigration = require database_path('migrations/2026_11_18_000002_create_notifications_table.php');
        $notificationMigration->up();
        $transferStatusMigration = require database_path('migrations/2026_11_18_000003_expand_financial_transfer_status.php');
        $transferStatusMigration->up();

        foreach (['invoice_reference', 'invoice_issued_on', 'invoice_due_on'] as $column) {
            $this->assertTrue(Schema::hasColumn('financial_settlements', $column), "Expected {$column} invoice column.");
        }
        $this->assertTrue(Schema::hasTable('notifications'));

        $countryId = DB::table('countries')->insertGetId([]);
        $mpoId = DB::table('admin_users')->insertGetId([]);
        $globalAdminId = DB::table('admin_users')->insertGetId([
            'admin_type' => 'global',
        ]);
        $operatorId = DB::table('operators')->insertGetId([]);
        $otherOperatorId = DB::table('operators')->insertGetId([]);
        $this->assertFalse(Route::has('mpo.accounting-settlements.transfers.verify-operator'));
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
        $operatorRecipient = new Operator();
        $operatorRecipient->setAttribute('id', $operatorId);
        Notification::assertSentToTimes($operatorRecipient, FinancialSettlementTransferNotification::class, 1);
        $operatorNotification = Notification::sent($operatorRecipient, FinancialSettlementTransferNotification::class)->first();
        $notificationData = $operatorNotification->toDatabase($operatorRecipient);
        $this->assertSame('financial_settlement_transfer_initiated', $notificationData['type']);
        $this->assertSame($operatorSettlement->reference, $notificationData['settlement_reference']);
        $this->assertSame('USD', $notificationData['currency']);
        $this->assertSame('40.00', $notificationData['amount']);
        $this->assertSame(route('operator.payouts.show', $operatorSettlement), $notificationData['url']);
        $otherOperatorRecipient = new Operator();
        $otherOperatorRecipient->setAttribute('id', $otherOperatorId);
        Notification::assertNotSentTo($otherOperatorRecipient, FinancialSettlementTransferNotification::class);
        $this->assertSame('Transfer Initiated', DB::table('financial_settlement_transfers')->where('id', $firstTransferId)->value('status'));
        try {
            $service->recordTransfer($operatorSettlement, $this->transferData('40.00'), 'mpo', $mpoId);
            $this->fail('The same transaction reference must not create duplicate transfer notifications.');
        } catch (HttpException $exception) {
            $this->assertSame(409, $exception->getStatusCode());
        }
        try {
            $service->verifyTransfer($operatorSettlement, $firstTransferId, 'mpo', $mpoId);
            $this->fail('The MPO must not confirm receipt of its own transfer.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
        try {
            $service->verifyTransfer($operatorSettlement, $firstTransferId, 'operator', $otherOperatorId);
            $this->fail('A different Operator must not verify this payout.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
        $service->verifyTransfer($operatorSettlement, $firstTransferId, 'operator', $operatorId);
        $this->assertSame('Partially Paid', $operatorSettlement->fresh()->status);
        $firstTransfer = DB::table('financial_settlement_transfers')->where('id', $firstTransferId)->first();
        $this->assertSame('mpo', $firstTransfer->recorded_by_type);
        $this->assertSame($mpoId, (int) $firstTransfer->recorded_by_id);
        $this->assertSame('operator', $firstTransfer->verified_by_type);
        $this->assertSame($operatorId, (int) $firstTransfer->verified_by_id);
        $this->assertNotNull($firstTransfer->verified_at);
        $this->assertSame('Transfer Verified', $firstTransfer->status);
        $this->assertSame('Transfer Initiated', DB::table('financial_settlement_events')->where('transfer_id', $firstTransferId)->where('event_type', 'transfer_recorded')->value('to_status'));
        try {
            $service->verifyTransfer($operatorSettlement, $firstTransferId, 'operator', $operatorId);
            $this->fail('A transfer must not be verified twice.');
        } catch (HttpException $exception) {
            $this->assertSame(409, $exception->getStatusCode());
        }

        $service->recordTransfer($operatorSettlement, $this->transferData('49.50'), 'mpo', $mpoId);
        $secondTransferId = (int) DB::table('financial_settlement_transfers')->orderByDesc('id')->value('id');
        try {
            $service->verifyTransfer($operatorSettlement, $secondTransferId, 'global_admin', $mpoId);
            $this->fail('HIO must not verify an Operator payout.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
        $service->verifyTransfer($operatorSettlement, $secondTransferId, 'operator', $operatorId);
        $this->assertSame('Paid', $operatorSettlement->fresh()->status);

        $pendingSettlementId = DB::table('financial_settlements')->insertGetId([
            'reference' => 'OP-TEST-PENDING',
            'generated_key' => 'operator-test-pending',
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
            'status' => 'Pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $pendingTransferId = DB::table('financial_settlement_transfers')->insertGetId([
            'financial_settlement_id' => $pendingSettlementId,
            'transfer_reference' => 'PENDING-TRANSFER',
            'transaction_number' => 'TX-PENDING',
            'transferred_on' => '2026-11-01',
            'amount' => 20,
            'currency' => 'USD',
            'status' => 'Transfer Initiated',
            'evidence_path' => 'test/evidence.pdf',
            'recorded_by_type' => 'mpo',
            'recorded_by_id' => $mpoId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        try {
            $service->verifyTransfer(
                \App\Models\FinancialSettlement::query()->findOrFail($pendingSettlementId),
                $pendingTransferId,
                'operator',
                $operatorId,
            );
            $this->fail('A pending settlement must not be verified before an initiated status transition.');
        } catch (HttpException $exception) {
            $this->assertSame(409, $exception->getStatusCode());
        }

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
        $hioRecipient = new AdminUser();
        $hioRecipient->setAttribute('id', $globalAdminId);
        Notification::assertSentToTimes($hioRecipient, FinancialSettlementTransferNotification::class, 1);
        try {
            $service->verifyTransfer($hioSettlement, $hioTransferId, 'mpo', $mpoId);
            $this->fail('The MPO must not verify an HIO payment.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
        try {
            $service->verifyTransfer($hioSettlement, $hioTransferId, 'operator', $operatorId);
            $this->fail('An Operator must not verify an HIO payment.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
        $service->verifyTransfer($hioSettlement, $hioTransferId, 'global_admin', $globalAdminId);
        $this->assertSame('Received', $hioSettlement->fresh()->status);
        $mpoRecipient = new AdminUser();
        $mpoRecipient->setAttribute('id', $mpoId);
        Notification::assertSentToTimes($mpoRecipient, FinancialSettlementTransferNotification::class, 3);
        $service->acknowledgeHioReceipt($hioSettlement, 'global_admin', $globalAdminId);
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
