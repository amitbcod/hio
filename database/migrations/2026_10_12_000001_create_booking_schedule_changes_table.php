<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_schedule_changes', function (Blueprint $table): void {
            $table->id();
            $table->string('service_type', 32);
            $table->unsignedBigInteger('service_booking_id');
            $table->json('previous_schedule');
            $table->json('new_schedule');
            $table->text('reason');
            $table->string('actor_type');
            $table->unsignedBigInteger('actor_id');
            $table->string('actor_role', 32);
            $table->timestamps();

            $table->index(
                ['service_type', 'service_booking_id', 'created_at'],
                'booking_schedule_changes_service_booking_created_idx'
            );
            $table->index(['actor_type', 'actor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_schedule_changes');
    }
};
