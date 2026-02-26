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
        Schema::create('activity_operations_staffing', function (Blueprint $table) {
            $table->id('operation_id');
            $table->unsignedBigInteger('activity_id');
            $table->string('service_id', 50)->nullable();
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->string('variant_equipment_id', 100)->nullable();
            $table->json('age_groups'); // Multi-select: Adults, Teens, Children, Infant
            $table->text('pickup_options')->nullable();
            $table->text('dropoff_options')->nullable();
            $table->json('accessibility_features')->nullable(); // Multi-select: ramps, seating, etc.
            $table->string('ops_contact_name')->nullable();
            $table->string('ops_contact_mobile')->nullable();
            $table->integer('crew_guide_count')->nullable();
            $table->text('crew_guide_requirements')->nullable();
            $table->text('special_equipment_notes')->nullable();
            $table->timestamps();

            $table->foreign('activity_id')
                  ->references('id')
                  ->on('activities')
                  ->onDelete('cascade');

            $table->index('activity_id');
            $table->unique('operation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_operations_staffing');
    }
};
