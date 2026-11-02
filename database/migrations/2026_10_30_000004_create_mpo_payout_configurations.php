<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mpo_payout_configurations')) {
            Schema::create('mpo_payout_configurations', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('admin_user_id')->unique()->constrained('admin_users')->restrictOnDelete();
                $table->string('payout_id', 32)->nullable()->unique();
                $table->string('payment_schedule', 32)->nullable();
                $table->string('payout_method', 16)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('mpo_payout_configuration_events')) {
            Schema::create('mpo_payout_configuration_events', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('payout_configuration_id')->constrained('mpo_payout_configurations')->restrictOnDelete();
                $table->foreignId('admin_user_id')->constrained('admin_users')->restrictOnDelete();
                $table->string('event_type', 32);
                $table->string('actor_type', 24);
                $table->unsignedBigInteger('actor_id')->nullable();
                $table->json('changes')->nullable();
                $table->timestamps();
                $table->index(['admin_user_id', 'created_at'], 'mpo_payout_event_owner_date_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mpo_payout_configuration_events');
        Schema::dropIfExists('mpo_payout_configurations');
    }
};
