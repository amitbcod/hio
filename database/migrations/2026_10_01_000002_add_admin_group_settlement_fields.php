<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bookings') && !Schema::hasColumn('bookings', 'is_admin_created')) {
            Schema::table('bookings', function (Blueprint $table): void {
                $table->boolean('is_admin_created')->default(false);
            });
        }

        if (Schema::hasTable('payment_transactions')) {
            Schema::table('payment_transactions', function (Blueprint $table): void {
                if (!Schema::hasColumn('payment_transactions', 'settlement_status')) {
                    $table->string('settlement_status', 40)->nullable()->index();
                }
                if (!Schema::hasColumn('payment_transactions', 'bank_transfer_date')) {
                    $table->date('bank_transfer_date')->nullable();
                }
                if (!Schema::hasColumn('payment_transactions', 'bank_transfer_receipt_path')) {
                    $table->string('bank_transfer_receipt_path')->nullable();
                }
                if (!Schema::hasColumn('payment_transactions', 'admin_notes')) {
                    $table->text('admin_notes')->nullable();
                }
                if (!Schema::hasColumn('payment_transactions', 'submitted_by')) {
                    $table->unsignedBigInteger('submitted_by')->nullable();
                }
                if (!Schema::hasColumn('payment_transactions', 'submitted_at')) {
                    $table->timestamp('submitted_at')->nullable();
                }
                if (!Schema::hasColumn('payment_transactions', 'verified_by')) {
                    $table->unsignedBigInteger('verified_by')->nullable();
                }
                if (!Schema::hasColumn('payment_transactions', 'verified_at')) {
                    $table->timestamp('verified_at')->nullable();
                }
            });
        }

        if (Schema::hasTable('bookings')
            && Schema::hasTable('booking_refs')
            && Schema::hasColumn('bookings', 'booking_ref_id')
            && Schema::hasColumn('bookings', 'booking_type')
            && Schema::hasColumn('bookings', 'is_admin_created')
            && Schema::hasColumn('booking_refs', 'idempotency_key')) {
            DB::table('bookings as bookings')
                ->join('booking_refs', 'booking_refs.id', '=', 'bookings.booking_ref_id')
                ->where('bookings.booking_type', 'open-group')
                ->whereNotNull('booking_refs.idempotency_key')
                ->update(['bookings.is_admin_created' => true]);
        }
    }

    public function down(): void
    {
        // Preserve settlement and audit data if this migration is rolled back.
    }
};