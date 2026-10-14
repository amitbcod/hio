<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_action_deadline_settings', function (Blueprint $table): void {
            $table->string('action_type', 50)->primary();
            $table->unsignedInteger('interval_hours')->default(24);
            $table->unsignedBigInteger('updated_by_admin_id')->nullable();
            $table->timestamps();
        });

        Schema::create('trip_action_deadlines', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('trip_id');
            $table->string('service_type', 30);
            $table->unsignedBigInteger('service_booking_id');
            $table->string('action_type', 50);
            $table->string('action_key', 191)->unique();
            $table->string('label', 255);
            $table->dateTime('target_at')->nullable();
            $table->dateTime('deadline_at')->nullable();
            $table->string('time_zone', 64)->nullable();
            $table->unsignedInteger('override_hours')->nullable();
            $table->string('status', 20)->default('open');
            $table->dateTime('completed_at')->nullable();
            $table->unsignedBigInteger('changed_by_admin_id')->nullable();
            $table->dateTime('changed_at')->nullable();
            $table->timestamps();

            $table->index(['trip_id', 'status']);
            $table->index(['service_type', 'service_booking_id']);
            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_action_deadlines');
        Schema::dropIfExists('trip_action_deadline_settings');
    }
};
