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
        Schema::create('activity_allotments', function (Blueprint $table) {
            $table->id('allotment_id');
            $table->string('service_id')->nullable();
            $table->unsignedBigInteger('activity_id');
            $table->string('service_name')->nullable();
            $table->unsignedBigInteger('variant_id');
            $table->string('variant_name')->nullable();
            $table->enum('participant_equipment_id', ['Per Person', 'Per Equipment']);
            $table->enum('allotment_strategy', ['Per Slot', 'Daily Cap', 'Equipment-based']);
            $table->text('slot_times')->nullable();
            $table->date('inventory_date');
            $table->unsignedInteger('allotment');
            $table->boolean('calendar_enabled')->default(false);
            $table->date('calendar_start')->nullable();
            $table->date('calendar_end')->nullable();
            $table->string('season')->nullable();
            $table->timestamps();

            $table->index('activity_id');
            $table->index('variant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_allotments');
    }
};
