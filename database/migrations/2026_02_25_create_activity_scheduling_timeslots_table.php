<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activity_scheduling_timeslots', function (Blueprint $table) {
            $table->id('timeslot_id');
            $table->string('service_id', 50); // Service code (e.g., SVC3C3B83B4)
            $table->unsignedBigInteger('activity_id');
            $table->unsignedBigInteger('variant_id');
            $table->string('service_name', 255)->nullable();
            $table->string('variant_name', 255)->nullable();
            $table->enum('participant_equipment_id', ['Per Person', 'Per Equipment'])->default('Per Person');
            $table->integer('capacity_per_slot')->default(1);
            $table->enum('schedule_type', ['Fixed Slots', 'Interval-Based', 'Open Booking', 'Group Events'])->default('Fixed Slots');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('duration', 50); // e.g., "2 Hours", "30 Minutes"
            $table->integer('recurring')->nullable(); // Number of times in a day
            $table->integer('lead_time_minutes')->nullable(); // Min minutes between slots
            $table->json('days_of_week')->nullable(); // Array: ['Monday', 'Tuesday', ...]
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            // Foreign keys
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->foreign('variant_id')->references('variant_id')->on('activity_variants')->onDelete('cascade');
            
            // Indexes
            $table->index('service_id');
            $table->index('activity_id');
            $table->index('variant_id');
            $table->index('schedule_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_scheduling_timeslots');
    }
};
