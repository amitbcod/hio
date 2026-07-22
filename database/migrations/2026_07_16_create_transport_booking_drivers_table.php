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
        if (!Schema::hasTable('transport_booking_drivers')) {
            Schema::create('transport_booking_drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transport_booking_id')->constrained('transport_bookings')->onDelete('cascade');
            $table->foreignId('operator_driver_id')->constrained('operator_drivers')->onDelete('cascade');
            $table->timestamps();
            
            // Unique constraint to prevent duplicate assignments
            $table->unique(['transport_booking_id', 'operator_driver_id'], 'tbd_booking_driver_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transport_booking_drivers');
    }
};
