<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_booking_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transport_booking_id')->constrained('transport_bookings')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('transport_vehicles')->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('operator_drivers')->nullOnDelete();
            $table->string('other_vehicle_name')->nullable();
            $table->string('other_vehicle_license_number')->nullable();
            $table->enum('status', ['Current', 'Replaced', 'Unassigned'])->default('Current');
            $table->text('reason')->nullable();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('unassigned_at')->nullable();
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamps();

            $table->index(['transport_booking_id', 'status']);
            $table->index('assigned_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_booking_assignments');
    }
};