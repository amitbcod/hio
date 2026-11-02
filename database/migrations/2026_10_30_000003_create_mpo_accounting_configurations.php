<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('countries', 'vat_percentage')) {
            Schema::table('countries', function (Blueprint $table): void {
                $table->decimal('vat_percentage', 7, 4)->nullable();
            });
        }

        if (! Schema::hasTable('mpo_accounting_configurations')) {
            Schema::create('mpo_accounting_configurations', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('admin_user_id')->unique()->constrained('admin_users')->restrictOnDelete();
                $table->string('tax_id', 100)->nullable();
                $table->string('vat_number', 100)->nullable();
                $table->boolean('vat_exempted')->default(false);
                $table->text('bank_details')->nullable();
                $table->unsignedBigInteger('responsible_accountant_user_id')->nullable();
                $table->string('bank_details_changed_by_type', 24)->nullable();
                $table->unsignedBigInteger('bank_details_changed_by_id')->nullable();
                $table->timestamp('bank_details_changed_at')->nullable();
                $table->timestamps();
                $table->foreign('responsible_accountant_user_id', 'mpo_accountant_staff_fk')
                    ->references('id')
                    ->on('mpo_staff_users')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasTable('mpo_accounting_configuration_events')) {
            Schema::create('mpo_accounting_configuration_events', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('admin_user_id')->constrained('admin_users')->cascadeOnDelete();
                $table->string('event_type', 32);
                $table->string('actor_type', 24);
                $table->unsignedBigInteger('actor_id')->nullable();
                $table->json('changes')->nullable();
                $table->timestamps();
                $table->index(['admin_user_id', 'created_at'], 'mpo_accounting_event_history_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mpo_accounting_configuration_events');
        Schema::dropIfExists('mpo_accounting_configurations');

        if (Schema::hasColumn('countries', 'vat_percentage')) {
            Schema::table('countries', function (Blueprint $table): void {
                $table->dropColumn('vat_percentage');
            });
        }
    }
};
