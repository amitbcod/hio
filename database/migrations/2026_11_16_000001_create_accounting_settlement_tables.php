<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_settlements', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 50)->unique();
            $table->string('generated_key', 64)->unique();
            $table->string('settlement_type', 24);
            $table->foreignId('country_id')->constrained('countries')->restrictOnDelete();
            $table->foreignId('mpo_admin_user_id')->constrained('admin_users')->restrictOnDelete();
            $table->foreignId('operator_id')->nullable()->constrained('operators')->restrictOnDelete();
            $table->string('payment_schedule', 24);
            $table->date('period_start');
            $table->date('period_end');
            $table->char('currency', 3)->default('USD');
            $table->decimal('gross_amount', 14, 2)->default(0);
            $table->decimal('operator_commission_amount', 14, 2)->default(0);
            $table->decimal('hio_sla_amount', 14, 2)->default(0);
            $table->decimal('net_amount', 14, 2)->default(0);
            $table->string('status', 32)->default('Pending');
            $table->json('configuration_snapshot')->nullable();
            $table->string('created_by_type', 24)->nullable();
            $table->unsignedBigInteger('created_by_id')->nullable();
            $table->timestamps();
            $table->index(['settlement_type', 'country_id', 'period_start'], 'fin_settle_type_country_period_idx');
            $table->index(['mpo_admin_user_id', 'status'], 'fin_settle_mpo_status_idx');
            $table->index(['operator_id', 'status'], 'fin_settle_operator_status_idx');
        });

        Schema::create('financial_settlement_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('financial_settlement_id')->constrained('financial_settlements')->restrictOnDelete();
            $table->string('settlement_type', 24);
            $table->string('service_type', 24);
            $table->unsignedBigInteger('service_booking_id');
            $table->string('booking_reference', 100);
            $table->foreignId('booking_ref_id')->nullable()->constrained('booking_refs')->nullOnDelete();
            $table->foreignId('trip_id')->nullable()->constrained('trips')->nullOnDelete();
            $table->foreignId('operator_id')->constrained('operators')->restrictOnDelete();
            $table->date('service_date');
            $table->date('eligible_date');
            $table->char('currency', 3)->default('USD');
            $table->decimal('gross_amount', 14, 2);
            $table->string('agreement_reference', 50)->nullable();
            $table->string('agreement_type', 50)->nullable();
            $table->string('commission_type', 24)->nullable();
            $table->decimal('commission_value', 10, 4)->nullable();
            $table->decimal('commission_base_amount', 14, 2)->nullable();
            $table->decimal('operator_commission_amount', 14, 2)->nullable();
            $table->decimal('hio_sla_rate', 6, 4)->nullable();
            $table->decimal('hio_sla_amount', 14, 2)->nullable();
            $table->decimal('net_amount', 14, 2);
            $table->json('calculation_snapshot')->nullable();
            $table->timestamps();
            $table->unique(
                ['settlement_type', 'service_type', 'service_booking_id'],
                'financial_settlement_source_unique',
            );
            $table->index(['operator_id', 'eligible_date'], 'fin_settle_items_operator_date_idx');
            $table->index(['booking_ref_id', 'trip_id'], 'fin_settle_items_booking_trip_idx');
        });

        Schema::create('financial_settlement_transfers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('financial_settlement_id')->constrained('financial_settlements')->restrictOnDelete();
            $table->string('transfer_reference', 50)->unique();
            $table->string('transaction_number', 100);
            $table->date('transferred_on');
            $table->decimal('amount', 14, 2);
            $table->char('currency', 3)->default('USD');
            $table->string('status', 16)->default('Recorded');
            $table->string('evidence_path', 255);
            $table->text('notes')->nullable();
            $table->string('recorded_by_type', 24);
            $table->unsignedBigInteger('recorded_by_id');
            $table->string('verified_by_type', 24)->nullable();
            $table->unsignedBigInteger('verified_by_id')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->index(['financial_settlement_id', 'status'], 'fin_settle_transfers_status_idx');
        });

        Schema::create('financial_settlement_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('financial_settlement_id')->constrained('financial_settlements')->restrictOnDelete();
            $table->foreignId('transfer_id')->nullable()->constrained('financial_settlement_transfers')->nullOnDelete();
            $table->string('event_type', 32);
            $table->string('from_status', 32)->nullable();
            $table->string('to_status', 32)->nullable();
            $table->string('actor_type', 24);
            $table->unsignedBigInteger('actor_id');
            $table->text('remarks')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['financial_settlement_id', 'created_at'], 'fin_settle_events_created_idx');
        });

        Schema::create('financial_booking_reviews', function (Blueprint $table): void {
            $table->id();
            $table->string('service_type', 24);
            $table->unsignedBigInteger('service_booking_id');
            $table->foreignId('country_id')->constrained('countries')->restrictOnDelete();
            $table->foreignId('mpo_admin_user_id')->constrained('admin_users')->restrictOnDelete();
            $table->foreignId('operator_id')->constrained('operators')->restrictOnDelete();
            $table->string('booking_reference', 100);
            $table->string('reason_code', 40);
            $table->text('reason');
            $table->string('status', 16)->default('Open');
            $table->string('reviewed_by_type', 24)->nullable();
            $table->unsignedBigInteger('reviewed_by_id')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->unique(['service_type', 'service_booking_id'], 'financial_booking_review_source_unique');
            $table->index(['country_id', 'status'], 'fin_booking_reviews_country_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_booking_reviews');
        Schema::dropIfExists('financial_settlement_events');
        Schema::dropIfExists('financial_settlement_transfers');
        Schema::dropIfExists('financial_settlement_items');
        Schema::dropIfExists('financial_settlements');
    }
};
